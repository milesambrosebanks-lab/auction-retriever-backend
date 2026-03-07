<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\DonationMail;
use App\Models\Donation;

class DonationEnailSendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:donation-enail-send-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $donations = Donation::where('type', 'monthly')->get();

        foreach ($donations as $donation) {
            if ($donation->user && $donation->user->email) {
                if (Mail::to($donation->email)->send(new DonationMail($donation))) {
                    $this->info("Email sent to {$donation->email}");
                } else {
                    $this->error("Email not sent to {$donation->email}");
                }
            } else {
                $this->error("User or email not found for donation ID: {$donation->id}");
            }
        }

        $this->info('All donation emails have been sent successfully.');

        return 0;
    }

}
