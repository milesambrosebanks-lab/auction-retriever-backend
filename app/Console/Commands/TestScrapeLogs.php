<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScrapeLog;
use Illuminate\Support\Facades\Cache;

class TestScrapeLogs extends Command
{
    protected $signature = 'test:scrape-logs
                            {--status=success : success | failed | running}
                            {--attempt=1 : attempt number}
                             {--clear-running : off running log }';

    protected $description = 'Test scrape log entries — success, failed, running';

 public function handle(): void
{
    // ── Running clear করুন ──────────────────────────────────────
    if ($this->option('clear-running')) {
        $count = ScrapeLog::where('status', 'running')->count();

        ScrapeLog::where('status', 'running')->update([
            'status'        => 'failed',
            'message'       => 'Extraction stopped — manually terminated (test cleanup)',
            'error_message' => 'Manually stopped via test command',
            'finished_at'   => now(),
        ]);

        $this->warn("⚠ Cleared $count running log(s) → marked as failed");
        return;
    }

    $status  = $this->option('status');
    $attempt = (int) $this->option('attempt');

    match($status) {
        'success' => $this->fakeSuccess($attempt),
        'failed'  => $this->fakeFailed($attempt),
        'running' => $this->fakeRunning($attempt),
        default   => $this->error("Invalid status. Use: success | failed | running"),
    };
}

    // ── Success ──────────────────────────────────────────────────────────────
    private function fakeSuccess(int $attempt): void
    {
        $log = ScrapeLog::create([
            'source'        => 'bid4assets',
            'status'        => 'success',
            'total_scraped' => 209,
            'attempt'       => $attempt,
            'message'       => "Extraction completed successfully. 209 listings saved on attempt {$attempt}.",
            'error_message' => null,
            'started_at'    => now()->subSeconds(45),
            'finished_at'   => now(),
        ]);

        Cache::put('scrape_last_success_bid4assets', now()->toDateTimeString(), 86400 * 7);
        Cache::put('scrape_last_count_bid4assets', 209, 86400 * 7);

        $this->info("✓ Success log created — ID: {$log->id}");
        $this->line("  Message : {$log->message}");
        $this->line("  Duration: {$log->duration}");
    }

    // ── Failed ───────────────────────────────────────────────────────────────
    private function fakeFailed(int $attempt): void
    {
        $errors = [
            "Source returned 0 items — site may be blocking requests or structure changed",
            "Session initialization failed — CSRF token not found in page",
            "cURL error 28: Operation timed out after 30000 milliseconds",
            "Invalid response on page 3 after 2 retries",
        ];

        $errorMsg = $errors[array_rand($errors)];

        $log = ScrapeLog::create([
            'source'        => 'bid4assets',
            'status'        => 'failed',
            'total_scraped' => 0,
            'attempt'       => $attempt,
            'message'       => "Extraction failed after {$attempt} attempt(s). No data saved.",
            'error_message' => $errorMsg,
            'started_at'    => now()->subSeconds(30),
            'finished_at'   => now(),
        ]);

        Cache::put('scrape_last_failed_bid4assets', now()->toDateTimeString(), 86400 * 7);

        $this->error("✗ Failed log created — ID: {$log->id}");
        $this->line("  Message : {$log->message}");
        $this->line("  Error   : {$log->error_message}");
    }

    // ── Running ──────────────────────────────────────────────────────────────
    private function fakeRunning(int $attempt): void
    {
        $log = ScrapeLog::create([
            'source'        => 'bid4assets',
            'status'        => 'running',
            'total_scraped' => 0,
            'attempt'       => $attempt,
            'message'       => "Attempt {$attempt} of 3 — connecting to source...",
            'error_message' => null,
            'started_at'    => now(),
            'finished_at'   => null,
        ]);

        $this->warn("⟳ Running log created — ID: {$log->id}");
        $this->line("  Message : {$log->message}");
        $this->line("  Page will show 'In Progress...' and auto-refresh every 10s");
    }
}
