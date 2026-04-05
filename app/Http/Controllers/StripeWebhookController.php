<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class StripeWebhookController extends CashierController
{
    // ── Payment Success → Transaction save ──────────────────────────
    protected function handleInvoicePaymentSucceeded($payload)
    {
        $invoice = $payload['data']['object'];

        Transaction::firstOrCreate(
            ['trx_id' => $invoice['id']],
            [
                'invoice_id'         => $invoice['id'],
                'amount'             => $invoice['amount_paid'] / 100,
                'customer_id'        => $invoice['customer'],
                'status'             => $invoice['status'],
                'invoice_pdf'        => $invoice['invoice_pdf'] ?? null,
                'hosted_invoice_url' => $invoice['hosted_invoice_url'] ?? null,
            ]
        );

        // User এর subscription_status update করুন
        $this->syncUserStatus($invoice['customer'], 'active');

        Log::info("Webhook: invoice.payment_succeeded — " . $invoice['id']);
        return response()->json(['status' => 'success']);
    }

    // ── Payment Failed ───────────────────────────────────────────────
    protected function handleInvoicePaymentFailed($payload)
    {
        $invoice = $payload['data']['object'];

        Transaction::firstOrCreate(
            ['trx_id' => $invoice['id']],
            [
                'invoice_id'         => $invoice['id'],
                'amount'             => $invoice['amount_due'] / 100,
                'customer_id'        => $invoice['customer'],
                'status'             => 'failed',
                'invoice_pdf'        => $invoice['invoice_pdf'] ?? null,
                'hosted_invoice_url' => $invoice['hosted_invoice_url'] ?? null,
            ]
        );

        $this->syncUserStatus($invoice['customer'], 'past_due');

        Log::warning("Webhook: invoice.payment_failed — " . $invoice['id']);
        return response()->json(['status' => 'success']);
    }

    // ── Subscription Updated (pause/resume) ──────────────────────────
    protected function handleCustomerSubscriptionUpdated($payload)
    {
        $stripeSubscription = $payload['data']['object'];
        $stripeId           = $stripeSubscription['id'];
        $stripeStatus       = $stripeSubscription['status'];
        $pauseCollection    = $stripeSubscription['pause_collection'] ?? null;

        $subscription = Subscription::where('stripe_id', $stripeId)->first();

        if (!$subscription) {
            Log::warning("Webhook: subscription not found — " . $stripeId);
            return response()->json(['status' => 'not_found'], 404);
        }

        // Pause হলে status paused set করুন
        $newStatus = $pauseCollection ? 'paused' : $stripeStatus;

        $subscription->update([
            'stripe_status' => $newStatus,
            'ends_at'       => $stripeSubscription['cancel_at']
                ? \Carbon\Carbon::createFromTimestamp($stripeSubscription['cancel_at'])
                : null,
        ]);

        Log::info("Webhook: subscription.updated — {$stripeId} → {$newStatus}");
        return response()->json(['status' => 'success']);
    }

    // ── Subscription Deleted (cancelled) ─────────────────────────────
    protected function handleCustomerSubscriptionDeleted($payload)
    {
        $stripeSubscription = $payload['data']['object'];
        $stripeId           = $stripeSubscription['id'];

        $subscription = Subscription::where('stripe_id', $stripeId)->first();

        if ($subscription) {
            $subscription->update([
                'stripe_status' => 'canceled',
                'ends_at'       => now(),
            ]);

            // User status update
            $this->syncUserStatus($stripeSubscription['customer'], 'canceled');

            Log::info("Webhook: subscription.deleted — {$stripeId}");
        }

        return response()->json(['status' => 'success']);
    }

    // ── Subscription Paused ───────────────────────────────────────────
    protected function handleCustomerSubscriptionPaused($payload)
    {
        $stripeSubscription = $payload['data']['object'];
        $stripeId           = $stripeSubscription['id'];

        $subscription = Subscription::where('stripe_id', $stripeId)->first();

        if ($subscription) {
            $subscription->update(['stripe_status' => 'paused']);
            $this->syncUserStatus($stripeSubscription['customer'], 'paused');
            Log::info("Webhook: subscription.paused — {$stripeId}");
        }

        return response()->json(['status' => 'success']);
    }

    // ── Subscription Resumed ──────────────────────────────────────────
    protected function handleCustomerSubscriptionResumed($payload)
    {
        $stripeSubscription = $payload['data']['object'];
        $stripeId           = $stripeSubscription['id'];

        $subscription = Subscription::where('stripe_id', $stripeId)->first();

        if ($subscription) {
            $subscription->update(['stripe_status' => 'active']);
            $this->syncUserStatus($stripeSubscription['customer'], 'active');
            Log::info("Webhook: subscription.resumed — {$stripeId}");
        }

        return response()->json(['status' => 'success']);
    }

    // ── Trial Will End (3 days before) ────────────────────────────────
    protected function handleCustomerSubscriptionTrialWillEnd($payload)
    {
        $stripeSubscription = $payload['data']['object'];
        $stripeId           = $stripeSubscription['id'];

        Log::info("Webhook: trial_will_end — {$stripeId}");

        // এখানে email notification পাঠাতে পারবেন
        // Mail::to($user)->send(new TrialEndingMail($user));

        return response()->json(['status' => 'success']);
    }

    // ── Helper: User subscription_status sync ────────────────────────
    private function syncUserStatus(string $stripeCustomerId, string $status): void
    {
        $user = User::where('stripe_id', $stripeCustomerId)->first();

        if ($user) {
            $user->update(['subscription_status' => $status]);
            Log::info("User status synced: {$user->email} → {$status}");
        }
    }
}

