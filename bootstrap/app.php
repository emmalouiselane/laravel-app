<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (Throwable $e) {
            if (env('GITHUB_REPORTING_ENABLED') && app()->environment('production')) {
                try {
                    $title = 'Error: ' . substr($e->getMessage(), 0, 100);
                    $body = "### Error Message\n" . $e->getMessage() . "\n\n";
                    $body .= "### File\n" . $e->getFile() . ':' . $e->getLine() . "\n\n";
                    $body .= "### URL\n" . request()->fullUrl() . "\n\n";
                    $body .= "### Stack Trace\n```\n" . substr($e->getTraceAsString(), 0, 1500) . "\n```";

                    \Illuminate\Support\Facades\Http::withToken(env('GITHUB_TOKEN'))
                        ->post('https://api.github.com/repos/' . env('GITHUB_REPO') . '/issues', [
                            'title' => $title,
                            'body' => $body,
                            'labels' => ['bug', 'automated-report']
                        ]);
                } catch (Throwable $reportError) {
                    // Fail silently to avoid infinite loops if reporting fails
                }
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $statusCode = $e->getStatusCode();
            if (!view()->exists("errors.{$statusCode}")) {
                return response()->view('errors.generic', ['exception' => $e], $statusCode);
            }
        });
    })->create();
