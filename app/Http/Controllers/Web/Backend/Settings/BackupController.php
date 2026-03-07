<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{

    public function databaseBackup()
    {
        $config = config('database.connections.mysql');
        $databaseName = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        $host = $config['host'];

        $backupDir = storage_path('app/backup');
        if (!file_exists($backupDir) && !mkdir($backupDir, 0755, true) && !is_dir($backupDir)) {
            return response()->json(['error' => 'Failed to create backup directory'], 500);
        }

        $backupFilePath = "{$backupDir}/{$databaseName}_" . date('Y-m-d_H-i-s') . ".sql";

        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($databaseName),
            escapeshellarg($backupFilePath)
        );

        $output = [];
        $result = 0;
        exec($command, $output, $result);

        if ($result === 0 && file_exists($backupFilePath)) {
            return response()->download($backupFilePath)->deleteFileAfterSend(true);
        } else {
            Log::error('Database backup failed', [
                'command' => $command,
                'output' => $output,
                'result' => $result,
            ]);
            return response()->json(['error' => 'Database backup failed'], 500);
        }
    }
}
