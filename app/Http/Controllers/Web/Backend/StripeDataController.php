<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Stripe\StripeClient;
use Throwable;

class StripeDataController extends Controller
{
    protected StripeClient $stripe;
    protected int $defaultLimit = 25;

    public function __construct()
    {
        View::share('crud', 'Stripe');
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    private function paginateParams(Request $request): array
    {
        $limit = (int) $request->get('limit', $this->defaultLimit);
        $limit = max(5, min(100, $limit));

        $params = ['limit' => $limit];
        $filters = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
        ];

        $created = [];
        if (!empty($filters['start_date'])) {
            $created['gte'] = Carbon::parse($filters['start_date'])->startOfDay()->timestamp;
        }
        if (!empty($filters['end_date'])) {
            $created['lte'] = Carbon::parse($filters['end_date'])->endOfDay()->timestamp;
        }
        if (!empty($created)) {
            $params['created'] = $created;
        }

        if ($request->filled('starting_after')) {
            $params['starting_after'] = $request->get('starting_after');
        }
        if ($request->filled('ending_before')) {
            $params['ending_before'] = $request->get('ending_before');
        }

        return [$params, $limit, $filters];
    }

    public function customers(Request $request)
    {
        [$params, $limit, $filters] = $this->paginateParams($request);
        $items = collect();
        $hasMore = false;
        $error = null;

        try {
            $res = $this->stripe->customers->all($params);
            $items = collect($res->data);
            $hasMore = $res->has_more;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        return view('backend.layouts.stripe.customers', compact('items', 'hasMore', 'limit', 'error', 'params', 'filters'));
    }

    public function subscriptions(Request $request)
    {
        [$params, $limit, $filters] = $this->paginateParams($request);
        $params['expand'] = ['data.customer', 'data.items.data.price'];

        $items = collect();
        $hasMore = false;
        $error = null;

        try {
            $res = $this->stripe->subscriptions->all($params);
            $items = collect($res->data);
            $hasMore = $res->has_more;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }


        return view('backend.layouts.stripe.subscriptions', compact('items', 'hasMore', 'limit', 'error', 'params', 'filters'));
    }

    public function invoices(Request $request)
    {
        [$params, $limit, $filters] = $this->paginateParams($request);

        $items = collect();
        $hasMore = false;
        $error = null;

        try {
            $res = $this->stripe->invoices->all($params);
            $items = collect($res->data);
            $hasMore = $res->has_more;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        return view('backend.layouts.stripe.invoices', compact('items', 'hasMore', 'limit', 'error', 'params', 'filters'));
    }

    public function transactions(Request $request)
    {
        [$params, $limit, $filters] = $this->paginateParams($request);

        $items = collect();
        $hasMore = false;
        $error = null;

        try {
            $res = $this->stripe->balanceTransactions->all($params);
            $items = collect($res->data);
            $hasMore = $res->has_more;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        return view('backend.layouts.stripe.transactions', compact('items', 'hasMore', 'limit', 'error', 'params', 'filters'));
    }
}
