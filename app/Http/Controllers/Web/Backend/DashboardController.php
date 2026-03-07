<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'dashboard');
    }

    public function index()
    {

        $all_months = [
            'january',
            'february',
            'march',
            'april',
            'may',
            'june',
            'july',
            'august',
            'september',
            'october',
            'november',
            'december'
        ];

        $transactions = Transaction::select(
            DB::raw("MONTHNAME(created_at) as month"),
            DB::raw("SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as success_total"),
            DB::raw("SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_total")
        )
            ->groupBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    strtolower($item->month) => [
                        'success' => number_format($item->success_total, 2),
                        'pending' => number_format($item->pending_total, 2)
                    ]
                ];
            });

        $formatted_data = collect($all_months)->mapWithKeys(function ($month) use ($transactions) {
            return [
                $month => $transactions->get($month, [
                    'success' => '0.00',
                    'pending' => '0.00'
                ])
            ];
        });

        file_put_contents(
            public_path('transactions/' . auth('web')->user()->slug . '.json'),
            json_encode($formatted_data)
        );

        file_put_contents(public_path('transactions/' . auth('web')->user()->slug . '.json'), json_encode($formatted_data));

        return view('backend.layouts.dashboard');
    }
}
