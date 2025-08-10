@extends('layouts.app')

@section('title', 'Welcome')

@push('styles')
    <style>
        .login-container {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background-color: #f8fafc;
        }
        .google-icon {
            width: 18px;
            height: 18px;
            margin-right: 8px;
        }
    </style>
@endpush

@section('content')
    <div class="login-container">
        <x-bladewind::card class="max-w-md w-full">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">Welcome back!</h1>
            </div>

            <div class="text-center mb-4">
                <img src="{{ asset('images/profile-pic.png') }}" alt="Spark Lane's Avatar" class="h-16 w-auto mx-auto">
            </div>

            <!-- Google Sign In Button -->
            <div class="text-center mb-4">
                <x-bladewind::button
                    tag="a"
                    href="{{ route('auth.google') }}"
                    uppercasing="false"
                    button_text_css="text-base"
                >
                    <span class="flex">
                        <svg class="google-icon" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                            <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Continue with Google
                    </span>
                </x-bladewind::button>
            </div>

            <!-- Forgot Password Link -->
            <div class="text-center mt-6">
                <x-bladewind::button 
                    type="secondary"
                    size="small"
                    color="gray"
                    onclick="window.location='https://accounts.google.com/signin/v2/recoveryidentifier'"
                    uppercasing="false"
                    button_text_css="text-base"
                >
                    Forgot your password?
                </x-bladewind::button>
            </div>
        </x-bladewind::card>
    </div>
@endsection
