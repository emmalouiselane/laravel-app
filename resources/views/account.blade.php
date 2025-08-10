@extends('layouts.app')

@section('title', 'Account')

@push('styles')

@php
$timezones = [
    'Africa' => DateTimeZone::listIdentifiers(DateTimeZone::AFRICA),
    'America' => DateTimeZone::listIdentifiers(DateTimeZone::AMERICA),
    'Antarctica' => DateTimeZone::listIdentifiers(DateTimeZone::ANTARCTICA),
    'Arctic' => DateTimeZone::listIdentifiers(DateTimeZone::ARCTIC),
    'Asia' => DateTimeZone::listIdentifiers(DateTimeZone::ASIA),
    'Atlantic' => DateTimeZone::listIdentifiers(DateTimeZone::ATLANTIC),
    'Australia' => DateTimeZone::listIdentifiers(DateTimeZone::AUSTRALIA),
    'Europe' => DateTimeZone::listIdentifiers(DateTimeZone::EUROPE),
    'Indian' => DateTimeZone::listIdentifiers(DateTimeZone::INDIAN),
    'Pacific' => DateTimeZone::listIdentifiers(DateTimeZone::PACIFIC),
    'UTC' => [\DateTimeZone::UTC],
];

// Flatten the array while maintaining the structure for the view
$timezoneOptions = [];
foreach ($timezones as $region => $list) {
    if (is_array($list)) {
        foreach ($list as $timezone) {
            $timezoneOptions[$timezone] = str_replace('_', ' ', $timezone);
        }
    } else {
        $timezoneOptions[$list] = str_replace('_', ' ', $list);
    }
}
ksort($timezoneOptions);
@endphp

<style>
    .account-container {
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
<div class="account-container">
    <header class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold">Account</h1>
        <x-auth-buttons 
            :showDashboard="true"
            :showAccount="false"
            :showLogout="true"
        />
    </header>
    
    <div class="welcome-card">
        <div class="user-avatar">
            <img src="{{ $user->avatar }}" alt="User Avatar" class="w-full h-full object-cover rounded-full">
        </div>
        <h2 class="text-xl font-semibold text-center mb-2">Welcome back, {{ $user->name }}!</h2>
        <p class="text-gray-600 text-center mb-6">You're logged in with Google ({{ $user->email }})</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-medium mb-2 flex justify-between">Account Details
                    <x-bladewind::button.circle 
                        icon="pencil"
                        outline="true"
                        size="tiny"
                        onclick="document.getElementById('editAccountModal').classList.remove('hidden')"
                    >
                    </x-bladewind::button.circle>
                    
                    <!-- Simple Modal -->
                    <div id="editAccountModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                        <form id="timezoneForm" method="POST" action="{{ route('account.update-timezone') }}" class="bg-white rounded-lg w-full max-w-md">
                            @csrf
                            @method('PATCH')
                            <div class="p-4 border-b flex justify-between items-center">
                                <h3 class="font-semibold">Edit Account</h3>
                                <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                                    ✕
                                </button>
                            </div>
                            <div class="p-4">
                                @if($errors->any())
                                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                                        {{ $errors->first() }}
                                    </div>
                                @endif
                                <div class="mb-4">
                                    <label for="timezone" class="block text-sm font-medium mb-1">Timezone:</label>
                                    <select name="timezone" id="timezone" class="w-full p-2 border rounded select2">
                                        @foreach($timezoneOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('timezone', $user->timezone) === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="p-4 border-t flex justify-end space-x-2">
                                <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 text-sm bg-primary-500 text-white rounded hover:bg-primary-600">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    @push('scripts')
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            function closeModal() {
                                document.getElementById('editAccountModal').classList.add('hidden');
                            }

                            // Make closeModal available globally
                            window.closeModal = closeModal;

                            function initSelect2() {
                                // Destroy existing instance if it exists
                                if ($('#timezone').hasClass('select2-hidden-accessible')) {
                                    $('#timezone').select2('destroy');
                                }

                                // Initialize Select2
                                $('#timezone').select2({
                                    theme: 'bootstrap-5',
                                    width: '100%',
                                    placeholder: 'Search for a timezone...',
                                    allowClear: true,
                                    dropdownParent: $('#editAccountModal')
                                });
                            }

                            // Initialize when clicking the edit button
                            document.querySelector('[onclick*="editAccountModal"]')?.addEventListener('click', function() {
                                // Small timeout to ensure the modal is visible
                                setTimeout(initSelect2, 100);
                            });

                            // Clean up on modal close
                            document.getElementById('editAccountModal')?.addEventListener('hidden.bs.modal', function () {
                                if ($('#timezone').hasClass('select2-hidden-accessible')) {
                                    $('#timezone').select2('destroy');
                                }
                            });
                        });
                    </script>
                    @endpush
                </h3>
                <p class="text-sm text-gray-600">Name: {{ $user->name }}</p>
                <p class="text-sm text-gray-600">Email: {{ $user->email }}</p>
                <p class="text-sm text-gray-600">Timezone: {{ $user->timezone }}</p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-medium mb-2">Authentication</h3>
                <p class="text-sm text-gray-600">Provider: Google</p>
                <p class="text-sm text-gray-600">Last login: {{ $user->updated_at->setTimezone($user->timezone)->format('M d, Y g:i A') }} </p>
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
                        <p class="text-xs text-gray-400">{{ $user->created_at->setTimezone($user->timezone)->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
