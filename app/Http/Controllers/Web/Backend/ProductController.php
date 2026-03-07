<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Helpers\Helper;
use App\Models\Product;
use App\Models\BundleItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CMS;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Stripe\Stripe;
use Stripe\Product as StripeProduct;
use Stripe\Price;


class ProductController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'product');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sectioninfo = CMS::where('page', 'product')->where('section', 'product-single')->where('slug', 'single')->orderBy('id', 'desc')->first();

        if ($request->ajax()) {
            $data = Product::query()->where('type', 'single')->orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('category', function ($data) {
                    if ($data->category_id === null) {
                        $data = "<span>Bundle</span>";
                    } else {
                        $data = "<span>$data->category_id</span>";
                    }
                    return $data;
                })
                ->addColumn('author', function ($data) {
                    return "<a href='" . route('admin.users.show', $data->user_id) . "'>" . $data->user->name . "</a>";
                })
                ->addColumn('title', function ($data) {
                    return Str::limit($data->name, 20);
                })
                ->addColumn('thumbnail', function ($data) {
                    $url = asset($data->thumbnail && file_exists(public_path($data->thumbnail)) ? $data->thumbnail : 'default/logo.svg');
                    return '<img src="' . $url . '" alt="image" style="width: 50px; max-height: 100px; margin-left: 20px;">';
                })
                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToEdit(' . $data->id . ')" class="btn btn-primary fs-14 text-white" title="edit">
                                    <i class="fa fa-edit"></i>
                                </a>

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-success fs-14 text-white " title="view">
                                    <i class="fa fa-eye"></i>
                                </a>

                            </div>';
                })
                ->rawColumns(['category', 'author', 'title', 'thumbnail', 'status', 'action'])
                ->make();
        }
        return view("backend.layouts.product.index", ['sectioninfo' => $sectioninfo, 'page' => 'product', 'section' => 'single', 'components' => ['title', 'sub_title']]);
    }
    public function bundle(Request $request)
    {
        $sectioninfo = CMS::where('page', 'product')->where('section', 'product-bundle')->where('slug', 'bundle')->orderBy('id', 'desc')->first();

        if ($request->ajax()) {
            $data = Product::query()->where('type', 'bundle')->orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('category', function ($data) {
                    if ($data->category_id === null) {
                        $data = "<span>Bundle</span>";
                    } else {
                        $data = "<span>$data->category_id</span>";
                    }
                    return $data;
                })
                ->addColumn('author', function ($data) {
                    return "<a href='" . route('admin.users.show', $data->user_id) . "'>" . $data->user->name . "</a>";
                })
                ->addColumn('title', function ($data) {
                    return Str::limit($data->name, 30);
                })
                ->addColumn('thumbnail', function ($data) {
                    $url = asset($data->thumbnail && file_exists(public_path($data->thumbnail)) ? $data->thumbnail : 'default/logo.svg');
                    return '<img src="' . $url . '" alt="image" style="width: 50px; max-height: 100px; margin-left: 20px;">';
                })
                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToEdit(' . $data->id . ')" class="btn btn-primary fs-14 text-white" title="edit">
                                    <i class="fa fa-edit"></i>
                                </a>

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-success fs-14 text-white " title="view">
                                    <i class="fa fa-eye"></i>
                                </a>

                            </div>';
                })
                ->rawColumns(['category', 'author', 'title', 'thumbnail', 'status', 'action'])
                ->make();
        }
        return view("backend.layouts.product.bundle", ['sectioninfo' => $sectioninfo, 'page' => 'product', 'section' => 'bundle', 'components' => ['title', 'sub_title']]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('status', 'active')->get();
        $brands = Brand::where('status', 'active')->get();
        return view('backend.layouts.product.create', compact('categories', 'brands'));
    }
    public function singleCreate()
    {
        $categories = Category::where('status', 'active')->get();
        $brands = Brand::where('status', 'active')->get();
        return view('backend.layouts.product.create_single', compact('categories', 'brands'));
    }

    /**
     * bundle product Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'title'             => 'nullable|max:250',
            'name'             => 'required|max:250',
            'one_time_price'             => 'required|numeric|min:0',
            'monthly_price'             => 'required|numeric|min:0',
            'description'       => 'nullable|string',
            // 'short_description'       => 'nullable|string',
            'stock'             => 'required|numeric|min:0',
            'supply_days'       => 'required|numeric|min:0',
            'thumbnail'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            // 'brand_id'          => 'nullable|exists:brands,id',
            'category_id'       => 'nullable|string',
            'include_item'      => 'nullable|array',
            'include_item.*'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try {
            $data = $validator->validated();

            $product = new Product();

            $product->user_id = auth('web')->user()->id;

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = Helper::fileUpload($request->file('thumbnail'), 'product', time() . '_' . getFileName($request->file('thumbnail')));
            }
            // dd(asset($data['thumbnail']));
            Stripe::setApiKey(config('services.stripe.secret'));

            // 1. Create Stripe Product
            $stripeProduct = StripeProduct::create([
                'name' => $request->name,
                'description' => $data['description'] ?? 'Not Set',
                'images' => [url($data['thumbnail']) ?? null]
            ]);

            // 2. One-time price
            $oneTimePrice = Price::create([
                'product' => $stripeProduct->id,
                'unit_amount' => $request->one_time_price * 100,
                'currency' => 'usd',
            ]);

            // 3. Monthly subscription price
            $monthlyPrice = Price::create([
                'product' => $stripeProduct->id,
                'unit_amount' => $request->monthly_price * 100,
                'currency' => 'usd',
                'recurring' => [
                    'interval' => 'month',
                ],
            ]);

            $product->slug = Helper::makeSlug(Product::class, $data['name']);

            $product->title = $data['title'];
            $product->name = $data['name'];
            $product->one_time_price = $data['one_time_price'];
            $product->monthly_price = $data['monthly_price'];
            $product->thumbnail = $data['thumbnail'] ?? null;
            $product->description = $data['description'];
            // $product->short_description = $data['short_description'];
            $product->stock = $data['stock'];
            $product->supply_days = $data['supply_days'];
            $product->category_id = $data['category_id'];
            $product->type = 'bundle';

            $product->stripe_product_id = $stripeProduct->id;
            $product->stripe_one_time_price_id = $oneTimePrice->id;
            $product->stripe_monthly_price_id = $monthlyPrice->id;
            $product->save();

            if ($request->has('include_item')) {
                collect($request->include_item)
                    ->filter() // remove empty values
                    ->each(function ($item) use ($product) {
                        $product->bundleItems()->create([
                            'title' => $item
                        ]);
                    });
            }

            // Update Stripe Metadata With Local Product ID
            StripeProduct::update($stripeProduct->id, [
                'metadata' => [
                    'local_product_id' => $product->id
                ]
            ]);
            DB::commit();

            return redirect()->route('admin.product.bundle')->with('t-success', 'product created successfully');
        } catch (Exception $e) {

            DB::rollBack();

            if (!empty($stripeProduct->id ?? null)) {
                try {
                    StripeProduct::update($stripeProduct->id, ['active' => false]);
                } catch (\Exception $cleanupException) {
                    Log::info($cleanupException);
                }
            }

            return redirect()->route('admin.product.bundle')->with('t-error', $e->getMessage());
        }

        // return redirect()->route('admin.product.bundle')->with('t-success', 'Data Added successfully');
    }
    public function singleStore(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'title'             => 'required|max:250',
            'name'             => 'required|max:250',
            // 'one_time_price'             => 'required|numeric|min:0',
            // 'monthly_price'             => 'required|numeric|min:0',
            'description'       => 'nullable|string',
            'short_description'       => 'nullable|string',
            // 'stock'             => 'required|numeric|min:0',
            // 'supply_days'       => 'required|numeric|min:0',
            'thumbnail'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'category_id'       => 'required|string',
            'include_item'      => 'nullable|array',
            'include_item.*'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $product = new Product();

            $product->user_id = auth('web')->user()->id;

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = Helper::fileUpload($request->file('thumbnail'), 'product', time() . '_' . getFileName($request->file('thumbnail')));
            }

            $product->slug = Helper::makeSlug(Product::class, $data['title']);
            $product->title = $data['title'];
            $product->name = $data['name'];
            $product->one_time_price = 0;
            $product->monthly_price = 0;
            $product->thumbnail = $data['thumbnail'];
            $product->description = $data['description'];
            $product->short_description = $data['short_description'];
            $product->type = 'single';
            // $product->stock = $data['stock'];
            // $product->supply_days = $data['supply_days'];
            $product->category_id = $data['category_id'];

            // $product->stripe_product_id = $stripeProduct->id;
            // $product->stripe_one_time_price_id = $oneTimePrice->id;
            // $product->stripe_monthly_price_id = $monthlyPrice->id;
            $product->save();

            if ($request->has('include_item')) {
                collect($request->include_item)
                    ->filter() // remove empty values
                    ->each(function ($item) use ($product) {
                        $product->bundleItems()->create([
                            'title' => $item
                        ]);
                    });
            }

            session()->put('t-success', 'product created successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.product.index')->with('t-success', 'Data Added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(product $product, $id)
    {
        $product = Product::with('user')->where('id', $id)->first();
        return view('backend.layouts.product.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(product $product, $id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('status', 'active')->get();
        return view('backend.layouts.product.edit', compact('product', 'categories'));
    }
    public function singleEdit(product $product, $id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('status', 'active')->get();
        return view('backend.layouts.product.edit_single', compact('product', 'categories'));
    }
    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'title'             => 'required|max:250',
    //         'name'             => 'required|max:250',
    //         'one_time_price'             => 'required|numeric|min:0',
    //         'monthly_price'             => 'required|numeric|min:0',
    //         'stock'             => 'required|numeric|min:0',
    //         'supply_days'       => 'required|numeric|min:0',
    //         'description'       => 'required|string',
    //         'thumbnail'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
    //         // 'category_id'       => 'required|exists:categories,id'
    //         'include_item'      => 'nullable|array',
    //         'include_item.*'    => 'nullable|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return redirect()->back()->withErrors($validator)->withInput();
    //     }

    //     try {
    //         $data = $validator->validated();

    //         $product = Product::findOrFail($id);

    //         if ($request->hasFile('thumbnail')) {
    //             $data['thumbnail'] = Helper::fileUpload($request->file('thumbnail'), 'product', time() . '_' . getFileName($request->file('thumbnail')));
    //         }

    //         $product->title             = $data['title'];
    //         $product->name             = $data['name'];
    //         $product->one_time_price = $data['one_time_price'];
    //         $product->monthly_price = $data['monthly_price'];
    //         $product->stock         = $data['stock'];
    //         $product->supply_days   = $data['supply_days'];
    //         $product->thumbnail     = $data['thumbnail'] ?? $product->thumbnail;
    //         $product->description   = $data['description'];
    //         $product->save();


    //         $existingIds = $product->bundleItems()->pluck('id')->toArray();
    //         $submittedIds = [];

    //         if ($request->has('include_item')) {

    //             foreach ($request->include_item as $key => $value) {

    //                 if (in_array($key, $existingIds)) {
    //                     // ✅ Update existing item
    //                     $product->bundleItems()
    //                         ->where('id', $key)
    //                         ->update(['title' => $value]);

    //                     $submittedIds[] = $key;
    //                 } else {
    //                     // ✅ Create new item
    //                     if (!empty($value)) {
    //                         $product->bundleItems()->create([
    //                             'title' => $value
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }

    //         // ✅ Delete removed items
    //         $itemsToDelete = array_diff($existingIds, $submittedIds);
    //         $product->bundleItems()->whereIn('id', $itemsToDelete)->delete();

    //         session()->put('t-success', 'product updated successfully');
    //     } catch (Exception $e) {

    //         session()->put('t-error', $e->getMessage());
    //     }

    //     return redirect()->route('admin.product.edit', $product->id)->with('t-success', 'data updated successfully');
    // }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'             => 'required|max:250',
            'name'             => 'required|max:250',
            'one_time_price'             => 'required|numeric|min:0',
            'monthly_price'             => 'required|numeric|min:0',
            'stock'             => 'required|numeric|min:0',
            'supply_days'       => 'required|numeric|min:0',
            'description'       => 'required|string',
            'thumbnail'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            // 'category_id'       => 'required|exists:categories,id'
            'include_item'      => 'nullable|array',
            'include_item.*'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();

        try {
            $data = $validator->validated();

            $product = Product::findOrFail($id);

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = Helper::fileUpload($request->file('thumbnail'), 'product', time() . '_' . getFileName($request->file('thumbnail')));
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            \Stripe\Product::update(
                $product->stripe_product_id, // your product ID
                [
                    'name' => $data['name'] ?? $product->name,
                    'description' => $data['description'] ?? $product->description,
                    'images' => isset($data['thumbnail']) ? [url($data['thumbnail'])] : ($product->thumbnail ? [url($product->thumbnail)] : [])
                ]
            );

            if ($request->one_time_price != $product->one_time_price) {

                \Stripe\Price::update($product->stripe_one_time_price_id, [
                    'active' => false
                ]);

                $newOneTimePrice = \Stripe\Price::create([
                    'product'     => $product->stripe_product_id,
                    'unit_amount' => (int) round($request->one_time_price * 100),
                    'currency'    => 'usd',
                ]);

                $product->stripe_one_time_price_id = $newOneTimePrice->id;
                $product->one_time_price = $request->one_time_price;
            }

            if ($request->monthly_price != $product->monthly_price) {

                \Stripe\Price::update($product->stripe_monthly_price_id, [
                    'active' => false
                ]);

                $newMonthlyPrice = \Stripe\Price::create([
                    'product'     => $product->stripe_product_id,
                    'unit_amount' => (int) round($request->monthly_price * 100),
                    'currency'    => 'usd',
                    'recurring'   => [
                        'interval' => 'month',
                    ],
                ]);

                $product->stripe_monthly_price_id = $newMonthlyPrice->id;
                $product->monthly_price = $request->monthly_price;
            }

            $product->title             = $data['title'];
            $product->name             = $data['name'];
            // $product->one_time_price = $data['one_time_price'];
            // $product->monthly_price = $data['monthly_price'];
            $product->stock         = $data['stock'];
            $product->supply_days   = $data['supply_days'];
            $product->thumbnail     = $data['thumbnail'] ?? $product->thumbnail;
            $product->description   = $data['description'];

            $product->save();


            $existingIds = $product->bundleItems()->pluck('id')->toArray();
            $submittedIds = [];

            if ($request->has('include_item')) {

                foreach ($request->include_item as $key => $value) {

                    if (in_array($key, $existingIds)) {
                        // ✅ Update existing item
                        $product->bundleItems()
                            ->where('id', $key)
                            ->update(['title' => $value]);

                        $submittedIds[] = $key;
                    } else {
                        // ✅ Create new item
                        if (!empty($value)) {
                            $product->bundleItems()->create([
                                'title' => $value
                            ]);
                        }
                    }
                }
            }

            // ✅ Delete removed items
            $itemsToDelete = array_diff($existingIds, $submittedIds);
            $product->bundleItems()->whereIn('id', $itemsToDelete)->delete();

            DB::commit();

            return redirect()->route('admin.product.edit', $product->id)->with('t-success', 'product updated successfully');
        } catch (Exception $e) {
            Log::info($e->getMessage());
            DB::rollBack();

            return redirect()->route('admin.product.edit', $product->id)->with('t-error', $e->getMessage());
        }

        // return redirect()->route('admin.product.edit', $product->id)->with('t-success', 'data updated successfully');
    }


    public function singleUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'             => 'required|max:250',
            'name'             => 'required|max:250',
            // 'one_time_price'             => 'required|numeric|min:0',
            // 'monthly_price'             => 'required|numeric|min:0',
            // 'stock'             => 'required|numeric|min:0',
            // 'supply_days'       => 'required|numeric|min:0',
            'description'       => 'required|string',
            'short_description'       => 'nullable|string',
            'thumbnail'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            // 'category_id'       => 'required|exists:categories,id'
            'include_item'      => 'nullable|array',
            'include_item.*'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $product = Product::findOrFail($id);

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = Helper::fileUpload($request->file('thumbnail'), 'product', time() . '_' . getFileName($request->file('thumbnail')));
            }

            $product->title             = $data['title'];
            $product->name             = $data['name'];
            // $product->one_time_price = $data['one_time_price'];
            // $product->monthly_price = $data['monthly_price'];
            // $product->stock         = $data['stock'];
            // $product->supply_days   = $data['supply_days'];
            $product->thumbnail     = $data['thumbnail'] ?? $product->thumbnail;
            $product->description   = $data['description'];
            $product->short_description   = $data['short_description'];
            // $product->category_id = $data['category_id'];
            $product->save();


            $existingIds = $product->bundleItems()->pluck('id')->toArray();
            $submittedIds = [];

            if ($request->has('include_item')) {

                foreach ($request->include_item as $key => $value) {

                    if (in_array($key, $existingIds)) {
                        // ✅ Update existing item
                        $product->bundleItems()
                            ->where('id', $key)
                            ->update(['title' => $value]);

                        $submittedIds[] = $key;
                    } else {
                        // ✅ Create new item
                        if (!empty($value)) {
                            $product->bundleItems()->create([
                                'title' => $value
                            ]);
                        }
                    }
                }
            }

            // ✅ Delete removed items
            $itemsToDelete = array_diff($existingIds, $submittedIds);
            $product->bundleItems()->whereIn('id', $itemsToDelete)->delete();

            session()->put('t-success', 'product updated successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.product.edit.single', $product->id)->with('t-success', 'data updated successfully');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $data = Product::findOrFail($id);

            if ($data->thumbnail && file_exists(public_path($data->thumbnail))) {
                Helper::fileDelete(public_path($data->thumbnail));
            }

            $data->delete();
            return response()->json([
                'status' => 't-success',
                'message' => 'Your action was successful!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 't-error',
                'message' => 'Your action was successful!'
            ]);
        }
    }

    public function status(int $id): JsonResponse
    {
        $data = Product::findOrFail($id);
        if (!$data) {
            return response()->json([
                'status' => 't-error',
                'message' => 'Item not found.',
            ]);
        }
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();
        return response()->json([
            'status' => 't-success',
            'message' => 'Your action was successful!',
        ]);
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|max:191',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $products = Product::query()
            ->select('id', 'name', 'description')
            ->where('name', 'like', '%' . $request->q . '%')
            ->orWhere('description', 'like', '%' . $request->q . '%')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function filter(Request $request)
    {
        $sort_by = $request->sort_key;
        $name = $request->name;
        $min = $request->min;
        $max = $request->max;

        $products = Product::query();
        $products->where('status', 'active');

        if ($request->brands != null && $request->brands != "") {
            $brand_ids = explode(',', $request->brands);
            $products->whereIn('brand_id', $brand_ids);
        }

        if ($request->has('made_in') && $request->made_in != "") {
            $made_in = explode(',', $request->made_in);
            $products->whereIn('made_in', $made_in);
        }

        if ($name != null && $name != "") {
            $products->where('name', 'like', '%' . $name . '%');
        }

        if ($min != null && $min != "" && is_numeric($min)) {
            $products->where('unit_price', '>=', $min);
        }

        if ($max != null && $max != "" && is_numeric($max)) {
            $products->where('unit_price', '<=', $max);
        }

        /* switch ($sort_by) {
            case 'price_low_to_high':
                $products->orderBy('unit_price', 'asc');
                break;

            case 'price_high_to_low':
                $products->orderBy('unit_price', 'desc');
                break;

            case 'new_arrival':
                $products->orderBy('created_at', 'desc');
                break;

            case 'popularity':
                $products->orderBy('num_of_sale', 'desc');
                break;

            case 'top_rated':
                $products->orderBy('rating', 'desc');
                break;

            default:
                $products->orderBy('created_at', 'desc');
                break;
        } */

        $products = $products->paginate(15);
        return response()->json($products);
    }
}
