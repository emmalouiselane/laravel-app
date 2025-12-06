@extends('layouts.app')

@section('title', 'Server Error')

@section('content')
    <div class="min-h-[60vh] flex flex-col items-center justify-center p-4">
        <div class="text-center max-w-lg">
            <div class="mb-6 flex justify-center">
                <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center">
                    <x-bladewind::icon name="exclamation-triangle" class="h-12 w-12 text-red-500" />
                </div>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-2">Something went wrong</h1>

            <p class="text-gray-600 mb-8">
                We encountered an unexpected error on our servers. We've been notified and will look into it.
                Please try again later.
            </p>

            <a href="{{ url('/') }}">
                <x-bladewind::button type="primary" size="medium">
                    Return Home
                </x-bladewind::button>
            </a>
        </div>
    </div>
@endsection