<?php

namespace App\Http\Controllers\Web\Backend\Access;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\SubscriptionPlan;
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
        // $users = User::where('id', '!=', $user->id)->with('roles')->orderBy('id', 'desc')->paginate(25);
        $plans = SubscriptionPlan::orderBy('id', 'desc')->paginate(25);
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
        // $user = User::find($id);
        // $roles = Role::all();
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

            return redirect()->back()->with('t-success', 'User updated t-successfully');
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $plan = Plan::find($id);
        $stripe = new StripeService();

        if ($plan->stripe_price_id) {
            $stripe->archivePrice($plan->stripe_price_id);
        }

        if ($plan->stripe_product_id) {
            $stripe->archiveProduct($plan->stripe_product_id);
        }

        $plan->delete();
        return redirect()->back()->with('t-success', 'User deleted t-successfully');
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

    public function card($slug)
    {

        $user = User::where('slug', $slug)->first();
        $logoBase64 = base64_encode(file_get_contents(public_path('default/logo.png')));
        $whitelogoBase64 = base64_encode(file_get_contents(public_path('default/logo.png')));
        $backLogoBase64 = base64_encode(file_get_contents(public_path('default/logo.png')));

        $avatarPath = public_path(
            $user->avatar && file_exists(public_path($user->avatar)) ? $user->avatar : 'default/profile.jpg'
        );

        $avatarBase64 = base64_encode(file_get_contents($avatarPath));

        //for pdf
        /* $qrCode = base64_encode(QrCode::size(90)->generate(route('admin.users.card', $user->slug)));
        $pdf = Pdf::loadView('card.pdf', compact('user', 'logoBase64', 'whitelogoBase64', 'avatarBase64', 'qrCode', 'backLogoBase64'))->setPaper('a4', 'portrait');
        return $pdf->stream();  */

        //for web
        $qrCode = QrCode::size(90)->generate(route('admin.users.card', $user->slug));
        return view('card.web', compact('user', 'logoBase64', 'whitelogoBase64', 'avatarBase64', 'qrCode', 'backLogoBase64'));
    }
}
