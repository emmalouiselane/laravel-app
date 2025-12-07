<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e) {
            \Illuminate\Support\Facades\Log::info('GitHub Reporting: 405 Specific Handler Triggered');

            if (env('GITHUB_REPORTING_ENABLED')) {
                try {
                    $title = 'Error: Method Not Allowed (405)';
                    $body = "### Error Message\nMethod Not Allowed for URL: " . request()->fullUrl() . "\n\n";
                    $body .= "### File\n" . $e->getFile() . ':' . $e->getLine() . "\n\n";
                    $body .= "### Headers\n" . json_encode(request()->headers->all()) . "\n\n";

                    $response = \Illuminate\Support\Facades\Http::withToken(env('GITHUB_TOKEN'))
                        ->post('https://api.github.com/repos/' . env('GITHUB_REPO') . '/issues', [
                            'title' => $title,
                            'body' => $body,
                            'labels' => ['bug', 'automated-report', '405']
                        ]);

                    if (!$response->successful()) {
                        \Illuminate\Support\Facades\Log::error('GitHub Reporting API Failed: ' . $response->status());
                    }
                } catch (\Throwable $err) {
                    \Illuminate\Support\Facades\Log::error('GitHub Reporting 405 Exception: ' . $err->getMessage());
                }
            }
        });

        $exceptions->stopIgnoring(\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class);

        $exceptions->report(function (\Throwable $e) {
            \Illuminate\Support\Facades\Log::info('GitHub Reporting: Handler triggered for ' . get_class($e));

            if (env('GITHUB_REPORTING_ENABLED')) {
                try {
                    $title = 'Error: ' . substr($e->getMessage(), 0, 100);
                    $body = "### Error Message\n" . $e->getMessage() . "\n\n";
                    $body .= "### File\n" . $e->getFile() . ':' . $e->getLine() . "\n\n";
                    $body .= "### URL\n" . request()->fullUrl() . "\n\n";
                    $body .= "### Stack Trace\n```\n" . substr($e->getTraceAsString(), 0, 1500) . "\n```";

                    $response = \Illuminate\Support\Facades\Http::withToken(env('GITHUB_TOKEN'))
                        ->post('https://api.github.com/repos/' . env('GITHUB_REPO') . '/issues', [
                            'title' => $title,
                            'body' => $body,
                            'labels' => ['bug', 'automated-report']
                        ]);

                    if (!$response->successful()) {
                        \Illuminate\Support\Facades\Log::error('GitHub Reporting API Failed: ' . $response->status() . ' - ' . $response->body());
                    } else {
                        \Illuminate\Support\Facades\Log::info('GitHub Issue Created: ' . $response->status());
                    }
                } catch (\Throwable $reportError) {
                    \Illuminate\Support\Facades\Log::error('GitHub Reporting Exception: ' . $reportError->getMessage());
                }
            } else {
                \Illuminate\Support\Facades\Log::info('GitHub Reporting: Disabled in config');
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $statusCode = $e->getStatusCode();
            if (!view()->exists("errors.{$statusCode}")) {
                return response()->view('errors.generic', ['exception' => $e], $statusCode);
            }
        });
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'report-issue',
        ]);
    })->create();
