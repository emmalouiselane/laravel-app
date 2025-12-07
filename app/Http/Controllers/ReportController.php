<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        if ($request->isMethod('get')) {
            return redirect('/');
        }

        $request->validate([
            'message' => 'required|string',
            'url' => 'required|url',
            'exception_context' => 'nullable|string',
        ]);

        if (!env('GITHUB_REPORTING_ENABLED')) {
            return back()->with('error', 'Reporting is disabled.');
        }

        try {
            $body = "### User Reported Error\n" .
                "**Message**: {$request->message}\n" .
                "**URL**: {$request->url}\n" .
                "**User Agent**: {$request->userAgent()}";

            if ($request->filled('exception_context')) {
                $data = json_decode($request->exception_context, true);
                if ($data) {
                    $body .= "\n\n### System Exception Details\n" .
                        "**Error**: " . ($data['message'] ?? 'N/A') . "\n" .
                        "**Location**: " . ($data['file'] ?? 'N/A') . ":" . ($data['line'] ?? 'N/A') . "\n\n" .
                        "<details><summary>Stack Trace</summary>\n\n```\n" . ($data['trace'] ?? 'N/A') . "\n```\n</details>";
                }
            }

            $response = Http::withToken(env('GITHUB_TOKEN'))
                ->post('https://api.github.com/repos/' . env('GITHUB_REPO') . '/issues', [
                    'title' => 'User Report: ' . substr($request->message, 0, 50) . '...',
                    'body' => $body,
                    'labels' => ['bug', 'user-reported']
                ]);

            if ($response->successful()) {
                return back()->with('success', 'Report sent successfully. Thank you!');
            } else {
                Log::error('GitHub API Failed: ' . $response->body());
                return back()->with('error', 'Failed to send report to GitHub.');
            }
        } catch (\Exception $e) {
            Log::error('Report Exception: ' . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred.');
        }
    }
}
