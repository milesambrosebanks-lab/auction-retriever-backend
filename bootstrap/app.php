<?php

use App\Helpers\Helper;
use App\Http\Middleware\ApiAdminMiddleware;
use App\Http\Middleware\ApiActiveUserMiddleware;
use App\Http\Middleware\WebAdminMiddleware;
use App\Http\Middleware\WebAuthCheckMiddleware;
use App\Http\Middleware\ApiCustomerMiddleware;
use App\Http\Middleware\WebDeveloperMiddleware;
use App\Http\Middleware\ApiOtpVerifiedMiddleware;
use App\Http\Middleware\WebOtpVerifiedMiddleware;
use App\Http\Middleware\ApiRetailerMiddleware;
use App\Http\Middleware\WebStaffMiddleware;
use App\Models\ScrapeLog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware(['web'])->prefix('ajax')->name('ajax.')->group(base_path('routes/ajax.php'));
            Route::middleware(['web', 'web-developer'])->prefix('developer')->name('developer.')->group(base_path('routes/web-developer.php'));
            Route::middleware(['web'])->prefix('admin')->name('admin.')->group(base_path('routes/web-admin.php'));
            Route::middleware(['api'])->group(base_path('routes/api-stripe.php'));
            require base_path('routes/cmd.php');
            // require base_path('routes/plugins.php');
        }
    )
    ->withBroadcasting(
        __DIR__ . '/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['auth:api']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'web-developer'         => WebDeveloperMiddleware::class,
            'web-admin'             => WebAdminMiddleware::class,
            'web-staff'             => WebStaffMiddleware::class,
            'api-admin'             => ApiAdminMiddleware::class,
            'api-active-user'       => ApiActiveUserMiddleware::class,
            'api-customer'          => ApiCustomerMiddleware::class,
            'api-retailer'          => ApiRetailerMiddleware::class,
            'api-otp'               => ApiOtpVerifiedMiddleware::class,
            'web-otp'               => WebOtpVerifiedMiddleware::class,
            'check'                 => WebAuthCheckMiddleware::class,
            'role'                  => RoleMiddleware::class,
            'permission'            => PermissionMiddleware::class,
            'role_or_permission'    => RoleOrPermissionMiddleware::class
        ]);
        $middleware->validateCsrfTokens(except: [
            'payment/stripe/webhook',
            'stripe/webhook',
            'graphql',
        ]);
        $middleware->api([
            StartSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                if ($e instanceof ValidationException) {
                    return Helper::jsonErrorResponse($e->getMessage(), 422, $e->errors());
                }

                if ($e instanceof ModelNotFoundException) {
                    return Helper::jsonErrorResponse($e->getMessage(), 404);
                }

                if ($e instanceof AuthenticationException) {
                    return Helper::jsonErrorResponse($e->getMessage(), 401);
                }
                if ($e instanceof AuthorizationException) {
                    return Helper::jsonErrorResponse($e->getMessage(), 403);
                }
                // Dynamically determine the status code if available
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                return Helper::jsonErrorResponse($e->getMessage(), $statusCode);
            } else {
                return null;
            }
        });
    })->withSchedule(function (Schedule $schedule) {
        $schedule->command('weekly:digest')->weekly();
        $schedule->command('scrape:bid4assets')->dailyAt('02:00');
        $schedule->command('sync:auction-details --batch=100')->dailyAt('03:00');
        $schedule->command('auction-listings:cleanup')->dailyAt('03:30');

        $schedule->command('scrape:auction --limit=50 --max=500')
            ->dailyAt('04:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/scrape-auction.log'));

        // if running more than 30 minuite then shuild be failed
        $schedule->call(function () {
            ScrapeLog::where('status', 'running')
                ->where('started_at', '<', now()->subMinutes(30))
                ->update([
                    'status'        => 'failed',
                    'message'       => 'Extraction timed out — process may have crashed',
                    'error_message' => 'Auto-terminated: running for more than 30 minutes',
                    'finished_at'   => now(),
                ]);
        })->everyFifteenMinutes();
    })
    ->withCommands([
        __DIR__ . '/../app/Console/Commands',
        \App\Console\Commands\SyncAuctionDetails::class,
    ])->create();
