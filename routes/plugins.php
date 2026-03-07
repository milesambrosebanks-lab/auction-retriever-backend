<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\PluginController;

Route::middleware(['web'])->group(function () {
    Route::get('/plugins/index', [PluginController::class, 'index'])->name('plugins.index');
    Route::post('/plugins/upload', [PluginController::class, 'upload'])->name('plugins.upload');
    Route::get('/Plugins/download/{file}', [PluginController::class, 'download'])->name('plugins.download');
    Route::get('/plugins/delete/{file}', [PluginController::class, 'delete'])->name('plugins.delete');
    Route::get('/plugins/install/{plugin}', [PluginController::class, 'install'])->name('plugins.install');
    Route::get('/plugins/uninstall/{plugin}', [PluginController::class, 'uninstall'])->name('plugins.uninstall');
});

$folders = scandir(__DIR__ . '/../Plugins/');
foreach ($folders as $folder) {
    if ($folder === '.' || $folder === '..') {
        continue;
    }

    $pluginRoutesPath = base_path('Plugins/' . $folder . '/routes/route.php');
    if (file_exists($pluginRoutesPath) && is_readable($pluginRoutesPath)) {
        require $pluginRoutesPath;
    } else {
        throw new \RuntimeException("Plugin $folder does not have a readable route.php file");
    }
}
