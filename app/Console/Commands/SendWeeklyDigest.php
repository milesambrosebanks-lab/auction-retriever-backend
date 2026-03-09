<?php

namespace App\Console\Commands;

use App\Mail\WeeklyDigestMail;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weekly:digest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly digest email to active and trailing users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            Log::info('start - ' . $this->signature);

            $users = User::whereHas('subscriptions', function ($q) {
                $q->whereIn('stripe_status', ['active', 'trialing']);
            })->get();

            $listings = Listing::where('created_at', '>=', now()->subDays(7))->get();

            foreach ($users as $user) {

                try {

                    Log::info('send mail to - ' . $user->email);

                    Mail::to($user->email)->send(new WeeklyDigestMail($listings));

                    Log::info('mail sent successfully - ' . $user->email);
                } catch (\Exception $e) {

                    Log::error('mail failed for - ' . $user->email . ' | error: ' . $e->getMessage());
                }
            }

            Log::info('end - ' . $this->signature);
        } catch (\Exception $e) {

            Log::error('command failed - ' . $this->signature . ' | error: ' . $e->getMessage());
        }
    }
}
