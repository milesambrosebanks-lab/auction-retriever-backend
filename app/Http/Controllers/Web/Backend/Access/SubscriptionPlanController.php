<?php

namespace App\Http\Controllers\Web\Backend\Access;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Services\StripeService;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Stripe\Stripe;
use Stripe\Product as StripeProduct;
use Stripe\Price;

class SubscriptionPlanController extends Controller
{

    public function __construct()
    {
        View::share('crud', 'my plan');
    }

    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();
        $permissions = $user->getAllPermissions(); // collection of Permission models
        //    dd($permissions);
        // $users = User::where('id', '!=', $user->id)->with('roles')->orderBy('id', 'desc')->paginate(25);
        $plans = Plan::orderBy('id', 'desc')->paginate(25);
        return view('backend.layouts.access.my_plan.public', compact('plans', 'user'));
    }

    public function create()
    {
        return view('backend.layouts.access.my_plan.create', ['roles' => Role::all()]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name'             => 'required|max:250',
            'price'             => 'required|numeric|min:0',
            'trial_days' => 'required|numeric|min:1',
            'features'      => 'nullable|array',
            'features.*'    => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {

            if ($validator->fails()) {
                DB::rollBack();
                return redirect()->back()->withErrors($validator)->withInput();
            }
            Stripe::setApiKey(config('services.stripe.secret'));

            $stripeProduct = StripeProduct::create([
                'name' => $request->name,
            ]);

            $price = Price::create([
                'product' => $stripeProduct->id,
                'unit_amount' => $request->price * 100,
                'currency' => 'usd',
                'recurring' => [
                    'interval' => 'month',
                ],
            ]);

            $paln = new Plan();
            $paln->name = $request->name;
            $paln->price = $request->price;
            $paln->trial_days = $request->trial_days;
            $paln->interval = 'month';
            $paln->interval_count = 1;
            $paln->stripe_price_id = $price->id;
            $paln->stripe_product_id = $stripeProduct->id;
            $paln->save();

            if ($request->has('features')) {
                collect($request->features)->filter()
                    ->each(function ($name) use ($paln) {
                        $paln->features()->create([
                            'name' => $name
                        ]);
                    });
            }

            DB::commit();

            return redirect()->route('admin.my_plan.index')->with('t-success', 'Plan created successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());

            if (!empty($stripeProduct->id ?? null)) {
                try {
                    StripeProduct::update($stripeProduct->id, ['active' => false]);
                } catch (\Exception $cleanupException) {
                    Log::info($cleanupException);
                }
            }
            return redirect()->route('admin.my_plan.index')->with('t-error', 'Plan created Failed');
        }
    }

    public function show($id)
    {
        $user = User::with(['profile'])->find($id);
        return view('backend.layouts.access.my_plan.show', compact('user'));
    }

    public function edit($id)
    {

        $plan = Plan::find($id);
        return view('backend.layouts.access.my_plan.edit', compact('plan'));
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:250',
            'price' => 'required|numeric|min:0',
            'features'      => 'nullable|array',
            'features.*'    => 'nullable|string',

        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $plan = Plan::find($id);

            \Stripe\Product::update(
                $plan->stripe_product_id, // your product ID
                [
                    'name' => $request['name'] ?? $plan->name,
                ]
            );

            if ($request->price != $plan->price) {

                \Stripe\Price::update($plan->stripe_price_id, [
                    'active' => false
                ]);

                $price = \Stripe\Price::create([
                    'product'     => $plan->stripe_product_id,
                    'unit_amount' => (int) round($request->price * 100),
                    'currency'    => 'usd',
                    'recurring'   => [
                        'interval' => 'month',
                    ],
                ]);

                $plan->stripe_price_id = $price->id;
                $plan->price = $request->price;
            }


            $plan->update([
                'name' => $request->name,
                'price' => $request->price,
            ]);

            $plan->save();

            $existingIds = $plan->features()->pluck('id')->toArray();
            $submittedIds = [];

            if ($request->has('features')) {
                foreach ($request->features as $key => $value) {
                    if (in_array($key, $existingIds)) {
                        // ✅ Update existing item
                        $plan->features()
                            ->where('id', $key)
                            ->update(['name' => $value]);
                        $submittedIds[] = $key;
                    } else {
                        // ✅ Create new item
                        if (!empty($value)) {
                            $plan->features()->create([
                                'name' => $value
                            ]);
                        }
                    }
                }
            }
            // ✅ Delete removed items
            $itemsToDelete = array_diff($existingIds, $submittedIds);
            $plan->features()->whereIn('id', $itemsToDelete)->delete();

            DB::commit();

            return redirect()->back()->with('t-success', 'Plan updated successfully');
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $plan = Plan::find($id);

            if (!$plan) {
                return redirect()->back()->with('t-error', 'Plan not found');
            }

            $stripe = new StripeService();

            if ($plan->stripe_price_id) {
                try {
                    $stripe->archivePrice($plan->stripe_price_id);
                } catch (\Throwable $priceException) {
                    // If the price does not exist in Stripe anymore, keep going and just delete the local plan
                    Log::info('Stripe price delete skipped: ' . $priceException->getMessage());
                }
            }

            if ($plan->stripe_product_id) {
                try {
                    $stripe->archiveProduct($plan->stripe_product_id);
                } catch (\Throwable $productException) {
                    Log::info('Stripe product delete skipped: ' . $productException->getMessage());
                }
            }

            $plan->delete();
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return redirect()->back()->with('t-error', 'Plan deleted failed');
        }
        return redirect()->back()->with('t-success', 'Plan deleted successfully');
    }

    public function status(int $id)
    {
        $user = User::findOrFail($id);
        if (!$user) {
            redirect()->back()->with('t-error', 'User not found');
        }
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();
        session()->put('t-success', 'Status updated successfully');
        return view('backend.layouts.access.users.show', compact('user'));
    }
}
