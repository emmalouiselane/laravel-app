@extends('layouts.app')

@php
    $statusCode = $exception->getStatusCode();
    $message = $exception->getMessage() ?: 'An unexpected error occurred.';

    // Friendly titles based on common codes
    $title = match ($statusCode) {
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Page Not Found',
        419 => 'Page Expired',
        429 => 'Too Many Requests',
        503 => 'Service Unavailable',
        default => 'Error ' . $statusCode
    };
@endphp

@section('title', $title)

@section('content')
    <div class="min-h-[60vh] flex flex-col items-center justify-center p-4">
        <div class="text-center max-w-lg">
            <div class="mb-6 flex justify-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center">
                    <x-bladewind::icon name="exclamation-circle" class="h-12 w-12 text-gray-500" />
                </div>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $title }}</h1>

            <p class="text-gray-600 mb-8">
                {{ $message }}
            </p>

            <div class="mt-8 flex flex-col gap-4">
                <a href="{{ url('/') }}">
                    <x-bladewind::button type="primary" size="medium">
                        Return Home
                    </x-bladewind::button>
                </a>

                @if(env('GITHUB_REPORTING_ENABLED'))
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="text-sm text-gray-500 hover:text-gray-700 underline">
                            Report this issue
                        </button>

                        <div x-show="open" class="mt-4 text-left border p-4 rounded bg-gray-50">
                            <form action="{{ route('report.issue') }}" method="POST">
                                @csrf
                                <input type="hidden" name="url" value="{{ request()->fullUrl() }}">
                                @if(isset($exception))
                                                    <input type="hidden" name="exception_context" value="{{ json_encode([
                                        'message' => $exception->getMessage(),
                                        'file' => $exception->getFile(),
                                        'line' => $exception->getLine(),
                                        'trace' => mb_substr($exception->getTraceAsString(), 0, 2000)
                                    ]) }}">
                                @endif
                                <div class="mb-3">
                                    <label class="block text-xs font-medium text-gray-700">Describe what happened:</label>
                                    <textarea name="message" rows="3"
                                        class="w-full text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                        required></textarea>
                                </div>
                                <x-bladewind::button can_submit="true" size="small" class="w-full">
                                    Send Report
                                </x-bladewind::button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection