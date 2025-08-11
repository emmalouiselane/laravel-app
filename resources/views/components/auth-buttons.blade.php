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
        <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
            @csrf
            <x-bladewind::button 
                type="submit"
                type="secondary"
                size="small"
                uppercasing="false"
                button_text_css="text-xs"
                icon="lock-closed"
                can_submit="true"
            >
                {{ __('Log Out') }}
            </x-bladewind::button>
        </form>
        
        <!-- Mobile: Icon only -->
        <form method="POST" action="{{ route('logout') }}" class="md:hidden">
            @csrf
            <x-bladewind::button 
                type="submit"
                type="secondary"
                size="small"
                icon="lock-closed"
                class="mobile-auth-buttons"
                has_shadow="false"
                can_submit="true"
            />
        </form>
    @endif
</div>
