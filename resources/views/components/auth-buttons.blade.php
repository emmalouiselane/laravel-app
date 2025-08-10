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
        <x-bladewind::button 
            tag="a"
            href="{{ route('dashboard') }}"
            type="primary"
            size="small"
            uppercasing="false"
            button_text_css="text-xs"
            icon="chart-bar-square"
        >
            {{ __('Dashboard') }}
        </x-bladewind::button>
    @endif

    @if($showAccount)
        <x-bladewind::button 
            tag="a"
            href="{{ route('account') }}"
            type="primary"
            size="small"
            uppercasing="false"
            button_text_css="text-xs"
            icon="key"
        >
            {{ __('Account') }}
        </x-bladewind::button>
    @endif

    @if($showLogout)
        <x-bladewind::button 
            tag="a"
            href="{{ route('logout') }}"
            type="secondary"
            size="small"
            uppercasing="false"
            button_text_css="text-xs"
            icon="lock-closed"
        >
            {{ __('Log Out') }}
        </x-bladewind::button>
    @endif
</div>
