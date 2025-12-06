@push('styles')
<style>
    .mobile-auth-buttons {
        height: 34px;
        width: 30px;
        padding: 0;
    }
    .auth-desktop-only {
        display: none !important;
    }
    .auth-mobile-only {
        display: flex !important;
    }
    @media (min-width: 768px) {
        .auth-desktop-only {
            display: flex !important;
        }
        .auth-mobile-only {
            display: none !important;
        }
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
            button_text_css="text-xs auth-desktop-only"
            icon="chart-bar-square"
            class="auth-desktop-only"
            title="Dashboard"
        >
            {{ __('Dashboard') }}
        </x-bladewind::button>
        
        <!-- Mobile: Icon only -->
        <form method="POST" action="{{ route('dashboard') }}" class="auth-mobile-only">
            @csrf
            <x-bladewind::button 
                type="secondary"
                size="small"
                icon="chart-bar-square"
                class="mobile-auth-buttons"
                has_shadow="false"
                can_submit="true"   
                title="Dashboard"
            />
        </form>
    @endif

    @if($showAccount)
        <!-- Desktop: Full text + icon -->
        <x-bladewind::button 
            tag="a"
            href="{{ route('account') }}"
            type="primary"
            size="small"
            uppercasing="false"
            button_text_css="text-xs auth-desktop-only"
            icon="key"
            class="auth-desktop-only"
            title="Account"
        >
            {{ __('Account') }}
        </x-bladewind::button>
        
        <!-- Mobile: Icon only -->
        <form method="POST" action="{{ route('account') }}" class="auth-mobile-only">
            @csrf
            <x-bladewind::button 
                type="secondary"
                size="small"
                icon="key"
                class="mobile-auth-buttons"
                has_shadow="false"
                can_submit="true"   
                title="Account"
            />
        </form>
    @endif

    @if($showLogout)
        <!-- Desktop: Full text + icon -->
        <form method="POST" action="{{ route('logout') }}" class="auth-desktop-only">
            @csrf
            <x-bladewind::button 
                type="secondary"
                size="small"
                uppercasing="false"
                button_text_css="text-xs"
                icon="lock-closed"
                can_submit="true"
                title="Log Out"
            >
                {{ __('Log Out') }}
            </x-bladewind::button>
        </form>
        
        <!-- Mobile: Icon only -->
        <form method="POST" action="{{ route('logout') }}" class="auth-mobile-only">
            @csrf
            <x-bladewind::button 
                type="secondary"
                size="small"
                icon="lock-closed"
                class="mobile-auth-buttons"
                has_shadow="false"
                can_submit="true"   
                title="Log Out"
            />
        </form>
    @endif
</div>
