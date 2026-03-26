<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class RealtyBidScraper
{

    public function scrape($limit = 50, $page = 1)
    {
        $scraperPath = base_path('realtybid.cjs');

        $process = new Process([
            'node',
            $scraperPath,
            $limit,
            $page
        ]);

        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception("Puppeteer failed: " . $process->getErrorOutput());
        }

        return json_decode($process->getOutput(), true);
    }
}
