<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class StripeWebhookController extends CashierController
{
    protected function handleInvoicePaymentSucceeded($payload)
    {
        $invoice = $payload['data']['object'];

        //  Log::info($invoice);

        // example transaction store
        Transaction::firstOrCreate(
            [
                'trx_id' => $invoice['id']
            ],
            [
                'invoice_id' => $invoice['id'],
                'amount' => $invoice['amount_paid'] / 100,
                'customer_id' => $invoice['customer'],
                'status' => $invoice['status'],
                'invoice_pdf' => $invoice['invoice_pdf'],
                'hosted_invoice_url' => $invoice['hosted_invoice_url'],
            ]
        );

        return response()->json(['status' => 'success']);
    }
}
