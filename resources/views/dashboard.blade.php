@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')

<style>
    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }
    .welcome-card {
        background: white;
        border-radius: 0.75rem;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .user-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 600;
        color: #4b5563;
        margin: 0 auto 1rem;
    }
</style>

@endpush

@section('content')
<div class="dashboard-container">
    <header class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-bladewind::button 
                type="secondary"
                size="small"
                color="gray"
                can_submit="true"
            >
                {{ __('Log Out') }}
            </x-bladewind::button>
        </form>
    </header>
    
    <div class="welcome-card">
        <div class="user-avatar">
            <img src="{{ $user->avatar }}" alt="User Avatar" class="w-full h-full object-cover rounded-full">
        </div>
        <h2 class="text-xl font-semibold text-center mb-2">Welcome back, {{ $user->name }}!</h2>
        <p class="text-gray-600 text-center mb-6">You're logged in with Google ({{ $user->email }})</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-medium mb-2">Account Details
                    <x-bladewind::button.circle 
                        icon="pencil"
                        outline="true"
                        size="tiny"
                        disabled
                    >
                    </x-bladewind::button>
                </h3>
                <p class="text-sm text-gray-600">Name: {{ $user->name }}</p>
                <p class="text-sm text-gray-600">Email: {{ $user->email }}</p>
                <p class="text-sm text-gray-600">Timezone: {{ $user->timezone }}</p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-medium mb-2">Authentication</h3>
                <p class="text-sm text-gray-600">Provider: Google</p>
                <p class="text-sm text-gray-600">Last login: {{ $user->updated_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
        
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="font-medium mb-3">Recent Activity</h3>
            <div class="space-y-3">
                <div class="flex items-start">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                        <svg class="h-4 w-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-600">You successfully created your account!</p>
                        <p class="text-xs text-gray-400">{{ $user->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
