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

            <a class="mt-5" href="{{ url('/') }}">
                <x-bladewind::button type="primary" size="medium">
                    Return Home
                </x-bladewind::button>
            </a>
        </div>
    </div>
@endsection