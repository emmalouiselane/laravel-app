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

        <x-auth-buttons 
            :showDashboard="false"
            :showAccount="true"
            :showLogout="true"
        />
    </header>
    
    <div class="welcome-card">
        <div class="user-avatar">
            <img src="{{ $user->avatar }}" alt="User Avatar" class="w-full h-full object-cover rounded-full">
        </div>
        <h2 class="text-xl font-semibold text-center mb-2">Welcome back, {{ $user->name }}!</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="m-2">Daily planner with to-do list and habit tracker</p>
                <a href="{{ route('planner.index') }}">
                    <x-bladewind::button 
                        type="primary"
                        size="small"
                        uppercasing="false"
                        button_text_css="text-base"
                    >
                        {{ __('Daily Planner') }}
                    </x-bladewind::button>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
