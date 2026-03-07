<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class CheckPhpFiles extends Command
{
    protected $signature = 'check:phpfiles';
    protected $description = 'Check for leading spaces or blank lines before <?php in PHP files (excluding Blade views)';

    public function handle()
    {
        $finder = new Finder();
        $hasErrors = false;

        // Find all PHP files in the project
        $finder->files()
            ->in(base_path())
            ->name('*.php')
            ->exclude(['vendor', 'storage', 'bootstrap/cache']);

        foreach ($finder as $file) {
            $path = $file->getRealPath();
            $contents = file_get_contents($path);

            // Skip files that don't start with <?php
            if (!str_starts_with(trim($contents), '<?php')) {
                continue;
            }

            // Check for any content before <?php
            $firstPhpTag = strpos($contents, '<?php');
            $beforePhpTag = substr($contents, 0, $firstPhpTag);

            if (strlen($beforePhpTag) > 0) {
                $lineCount = substr_count($beforePhpTag, "\n") + 1;
                $hasErrors = true;

                // Format the error message
                $relativePath = str_replace(base_path() . '/', '', $path);
                $this->error("Whitespace found before <?php tag in:");
                $this->line("  <fg=red>• {$relativePath}</>");
                $this->line("    {$lineCount} line(s) of whitespace detected\n");
            }
        }

        if (!$hasErrors) {
            $this->info('✓ No files with leading whitespace found.');
        }

        return $hasErrors ? 1 : 0;
    }
}
