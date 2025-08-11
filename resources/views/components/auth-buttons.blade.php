@push('styles')
<style>
    .mobile-auth-buttons {
        height: 34px;
        width: 30px;
        padding: 0;
    }
</style>
@endpush

@props([
    'showDashboard' => null,
    'showAccount' => null,
    'showLogout' => null,
])

@php
    // Convert string 'true'/'false' to boolean
    $showDashboard = filter_var($showDashboard, FILTER_VALIDATE_BOOLEAN);
    $showAccount = filter_var($showAccount, FILTER_VALIDATE_BOOLEAN);
    $showLogout = $showLogout === null ? true : filter_var($showLogout, FILTER_VALIDATE_BOOLEAN);
@endphp

<div class="flex flex-row space-x-2">
    @if($showDashboard)
        <!-- Desktop: Full text + icon -->
        <x-bladewind::button 
            tag="a"
            href="{{ route('dashboard') }}"
            type="primary"
            size="small"
            uppercasing="false"
            button_text_css="text-xs hidden md:inline"
            icon="chart-bar-square"
            class="hidden md:flex"
        >
            {{ __('Dashboard') }}
        </x-bladewind::button>
        
        <!-- Mobile: Icon only -->
        <x-bladewind::button 
            tag="a"
            href="{{ route('dashboard') }}"
            type="primary"
            size="small"
            icon="chart-bar-square"
            class="md:hidden mobile-auth-buttons"
            has_shadow="false"
        />
    @endif

    @if($showAccount)
        <!-- Desktop: Full text + icon -->
        <x-bladewind::button 
            tag="a"
            href="{{ route('account') }}"
            type="primary"
            size="small"
            uppercasing="false"
            button_text_css="text-xs hidden md:inline"
            icon="key"
            class="hidden md:flex"
        >
            {{ __('Account') }}
        </x-bladewind::button>
        
        <!-- Mobile: Icon only -->
        <x-bladewind::button 
            tag="a"
            href="{{ route('account') }}"
            type="primary"
            size="small"
            icon="key"
            class="md:hidden mobile-auth-buttons"
            has_shadow="false"
        />
    @endif

    @if($showLogout)
        <!-- Desktop: Full text + icon -->
        <x-bladewind::button 
            tag="a"
            href="{{ route('logout') }}"
            type="secondary"
            size="small"
            uppercasing="false"
            button_text_css="text-xs hidden md:inline"
            icon="lock-closed"
            class="hidden md:flex"
        >
            {{ __('Log Out') }}
        </x-bladewind::button>
        
        <!-- Mobile: Icon only -->
        <x-bladewind::button 
            tag="a"
            href="{{ route('logout') }}"
            type="secondary"
            size="small"
            icon="lock-closed"
            class="md:hidden mobile-auth-buttons"
            has_shadow="false"
        />
    @endif
</div>
