<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;

class ReportController extends Controller
{
    public function dashboard()
    {
        $data = [];

        $current_date    = date('Y-m-d');
        $previous_date   = date('Y-m-d', strtotime('-1 day'));
        $cur_start_month = date('Y-m-1');
        $cur_end_month   = date('Y-m-t');
        $pre_start_month = date('Y-m-1', strtotime('-1 month'));
        $pre_end_month   = date('Y-m-t', strtotime('-1 month'));


        $today_sale     = Order::whereNotNull('tracking_number')
            ->where('status', 5)
            ->whereDate('order_date', '>=', $current_date)
            ->whereDate('order_date', '<=', $current_date);
        if (isset($user_area) && $user_area !== 0) {
            $today_sale = $today_sale->whereHas('shop', function ($query) use ($user_area) {
                $query->where('area_id', $user_area);
            });
        }
        $today_sale_in_taka = $today_sale->sum('paid_total');
        $today_sale_count    = $today_sale->count();

        $data['today_sale'] = $today_sale_in_taka;
        $data['today_sale_count'] = $today_sale_count;


        // Order amount month
        $monthly_order_amount = Order::whereNotNull('tracking_number')
            ->whereDate('order_date', '>=', $cur_start_month)
            ->whereDate('order_date', '<=', $cur_end_month);
        if (isset($user_area) && $user_area !== 0) {
            $monthly_order_amount = $monthly_order_amount->whereHas('shop', function ($query) use ($user_area) {
                $query->where('area_id', $user_area);
            });
        }
        $monthly_order_amount = $monthly_order_amount->sum('paid_total');

        return view('backend.layouts.reports.users');
    }

    public function analytics($start, $end)
    {
        $months = [
            1 => "January",
            2 => "February",
            3 => "March",
            4 => "April",
            5 => "May",
            6 => "June",
            7 => "July",
            8 => "August",
            9 => "September",
            10 => "October",
            11 => "November",
            12 => "December",
        ];

        $order = Order::whereNotNull('tracking_number')
            ->whereDate('order_date', '>=', $start)
            ->whereDate('order_date', '<=', $end)
            ->selectRaw('SUM(paid_total) as total, MONTH(order_date) as month')
            ->groupBy('month')
            ->get();

        // Initialize result with 0 for all months
        $result = [];
        foreach ($months as $num => $name) {
            $result[$name] = 0;
        }

        // Fill with actual totals
        foreach ($order as $row) {
            $monthName = $months[$row->month];
            $result[$monthName] = (float) $row->total;
        }

        $data = [];
        $data['orders'] = $order;
        $data['months'] = array_keys($result);
        $data['totals'] = array_values($result);

        return response()->json($result);
    }

    public function users()
    {
        return view('backend.layouts.reports.users');
    }
}
