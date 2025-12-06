@extends('layouts.app')

@section('title', 'Budget')

@push('styles')
<style>
    .budget-container {
        max-width: 1200px;
        width: 100%;
        margin: 0 auto;
        padding: 1rem;
    }
    .budget-header {
        margin: 1rem auto;
    }
    
    .date-display {
        font-size: 0.875rem;
        font-weight: 600;
    }

    .date-navigation {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 200px;
        justify-content: center;
    }
    
    .budget-item {
        display: flex;
        align-items: flex-start;
        padding: 1rem;
        background: white;
    }
    
    .budget-item.completed .budget-title {
        opacity: 0.7;
        color: gray;
        text-decoration: line-through;
    }
    
    .budget-checkbox {
        margin-right: 1rem;
        margin-top: 0.25rem;
    }
    
    .budget-content {
        flex: 1;
    }
    
    .budget-title {
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    
    .budget-description {
        color: #6b7280;
        font-size: 0.875rem;
    }
    
    .budget-time {
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    .budget-actions {
        margin: auto;
    }

    .settings-button {
        width: 30px;
        height: 28px;
        padding: 0;
    }

    @media (max-width: 640px) {
        /* Overlay panel margins for small screens */
        #add-payment-panel {
            left: 0.5rem;
            right: 0.5rem;
        }
        /* Edit modal full width */
        #editPaymentModal > div { width: 100% !important; max-width: 100% !important; }
    }
</style>
@endpush

@section('content')
<div class="budget-container relative">
    <header class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Budget</h1>

        <div class="flex items-center gap-2">
            <a href="{{ route('budget.settings') }}" class="settings-button bg-gray-100 text-gray-700 rounded hover:bg-gray-200 flex items-center justify-center" title="Settings">
                <x-bladewind::icon name="cog" class="size-4" />
            </a>
            <x-auth-buttons 
                :showDashboard="true"
                :showAccount="true"
                :showLogout="true"
            />
        </div>
    </header>
    
    <div class="budget-header grid grid-cols-4">
        <!-- Current -->
        <div class="flex items-center">
            <a href="{{ route('budget.index') }}" 
               class="px-3 py-1 rounded text-sm {{ $payPeriod['is_current'] ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-primary-100 text-primary-600 hover:bg-primary-200' }}"
               {{ $payPeriod['is_current'] ? 'disabled' : '' }}>
                Current
            </a>
        </div>

        <!-- Date Navigation (center) -->
        <div class="date-navigation flex items-center col-span-2">
            <a href="{{ route('budget.index', ['date' => $payPeriod['previous_period']]) }}" 
                style="width: 30px; height: 28px;" 
                class="px-2 bg-gray-100 rounded hover:bg-gray-200 flex items-center justify-center">
                <x-bladewind::icon name="arrow-left" class="size-4" />
            </a>
            <div class="text-center">
                <div class="date-display">
                    {{ $payPeriod['start_date']->format('j M') }} - {{ $payPeriod['end_date']->format('j M Y') }}
                </div>
            </div>
            <a href="{{ route('budget.index', ['date' => $payPeriod['next_period']]) }}" 
                style="width: 30px; height: 28px;" 
                class="px-2 bg-gray-100 rounded hover:bg-gray-200 flex items-center justify-center">
                <x-bladewind::icon name="arrow-right" class="size-4" />
            </a>
        </div>

        <!-- Add (right) -->
        <div class="flex justify-end items-center gap-2">
            <a disabled class="px-3 py-1 rounded text-sm bg-gray-100 text-gray-400 cursor-not-allowed">
                Charts
            </a>
        </div>
    </div>

    @if (session('success'))
        <div id="toast" class="fixed right-4 top-4 z-50 bg-green-600 text-white px-4 py-3 rounded shadow flex items-start gap-3">
            <div class="pt-0.5">
                <x-bladewind::icon name="check-circle" class="size-5" />
            </div>
            <div>{{ session('success') }}</div>
            <button type="button" id="toast-close" class="ml-2 text-white/80 hover:text-white">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 rounded bg-red-50 text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="add-payment-panel" class="{{ $errors->any() ? '' : 'hidden' }} absolute z-10 left-4 right-4 bg-white rounded shadow p-4">
        <div class="flex justify-between items-center mb-3">
            <h2 class="font-semibold">Add</h2>
            <button type="button" id="add-payment-close" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form action="{{ route('budget.payments.store') }}" method="POST" class="space-y-3">
            @csrf

            <div class="grid sm:grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label for="date" class="block text-sm font-medium mb-1">Date</label>
                    <input id="date" name="date" type="date" value="{{ old('date', now()->toDateString()) }}" class="border w-full rounded px-2 py-1" required>
                </div>
                <div>
                    <label for="name" class="block text-sm font-medium mb-1">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" class="border w-full rounded px-2 py-1" placeholder="e.g. Rent, Salary" required>
                </div>
                <div>
                    <label for="amount" class="block text-sm font-medium mb-1">Amount</label>
                    <input id="amount" name="amount" type="number" step="0.01" value="{{ old('amount') }}" class="border w-full rounded px-2 py-1" placeholder="0.00" required>
                </div>
                <div>
                    <label for="direction" class="block text-sm font-medium mb-1">Direction</label>
                    <select id="direction" name="direction" class="border w-full rounded px-2 py-1" required>
                        <option value="outgoing" {{ old('direction')==='outgoing' ? 'selected' : '' }}>outgoing</option>
                        <option value="incoming" {{ old('direction')==='incoming' ? 'selected' : '' }}>incoming</option>
                    </select>
                </div>
            </div>
            <div class="grid sm:grid-cols-1 md:grid-cols-4 gap-3">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" id="repeatable" name="repeatable" value="1" class="text-primary-600" {{ old('repeatable') ? 'checked' : '' }}>
                    <span class="text-sm">Repeatable</span>
                </label>
                <div id="repeat-fields" class="repeat-fields {{ old('repeatable') ? '' : 'opacity-50' }}">
                    <label for="frequency" class="block text-sm font-medium mb-1">Frequency</label>
                    <select id="frequency" name="frequency" class="border w-full rounded px-2 py-1" {{ old('repeatable') ? '' : 'disabled' }}>
                        <option value="">-- select --</option>
                        <option value="weekly" {{ (old('frequency')==='weekly') ? 'selected' : '' }}>weekly</option>
                        <option value="monthly" {{ (old('frequency')==='monthly') ? 'selected' : '' }}>monthly</option>
                        <option value="yearly" {{ (old('frequency')==='yearly') ? 'selected' : '' }}>yearly</option>
                    </select>
                </div>
                <div class="repeat-fields {{ old('repeatable') ? '' : 'opacity-50' }}">
                    <label for="repeat_end_date" class="block text-sm font-medium mb-1">Repeat Ends</label>
                    <input id="repeat_end_date" name="repeat_end_date" type="date" value="{{ old('repeat_end_date') }}" 
                            class="border w-full rounded px-2 py-1" {{ old('repeatable') ? '' : 'disabled' }}>
                </div>
            </div>     
            <div class="flex justify-end gap-2">
                <button type="submit" class="px-3 py-1 rounded text-sm bg-primary-600 text-white hover:bg-primary-700">Add</button>
            </div>       
        </form>
    </div>

    <!-- Totals Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
        @php
            function formatBudgetAmount($amount, $direction = null) {
                if ($direction === 'incoming') {
                    $sign = '+';
                } elseif ($direction === 'outgoing') {
                    $sign = '-';
                } else {
                    $sign = $amount == 0 ? '' : ($amount > 0 ? '+' : '-');
                }
                return $sign . '£' . number_format(abs((float)$amount), 2);
            }
        @endphp
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Incoming</div>
            <div class="text-lg font-semibold text-green-700">
                {{ formatBudgetAmount($incomingTotal) }}
            </div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Outgoing</div>
            <div class="text-lg font-semibold text-red-700">
                {{ formatBudgetAmount($outgoingTotal) }}
            </div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Net Leftover</div>
            <div class="text-lg font-semibold {{ $netTotal >= 0 ? 'text-green-700' : 'text-red-700' }}">
                {{ formatBudgetAmount($netTotal) }}
            </div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Remaining Unpaid</div>
            <div class="text-lg font-semibold {{ $remainingUnpaid >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                {{ formatBudgetAmount($remainingUnpaid) }}
            </div>
        </div>
    </div>

    <!-- Occurrences List -->
    <div class="budget-list space-y-3">
        <div class="flex justify-end items-center gap-2">
            <div id="add-payment-toggle" class="flex justify-end items-center gap-2 hidden">
                <button type="button" class="px-3 py-1 rounded text-sm bg-primary-600 text-white hover:opacity-90">
                    Add Payment
                </button>
            </div>
            <button type="button" id="management-mode-toggle"
                class="px-3 py-1 rounded text-sm bg-gray-400 text-white hover:opacity-90">
                Manage Payments
            </button>      
        </div>
            

        @forelse ($occurrences as $occurrence)
            @php($payment = $occurrence->payment)
            <div class="budget-item border rounded" data-management-mode="false">
                <div class="budget-content w-full">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <div class="flex gap-2 mb-2">
                                <span class="font-semibold">{{ $payment->name }}</span>
                                
                                <div class="status-controls flex items-center gap-1">
                                    <span class="text-xs px-2 py-0.5 rounded {{ $occurrence->status === 'paid' ? 'bg-green-50 text-green-700' : ($occurrence->status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                        {{ ucfirst($occurrence->status) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                @if($payment->repeatable)
                                    <span class="text-xs px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 mt-1">{{ ucfirst($payment->frequency ?? 'repeat') }}</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700 mt-1">One-off</span>
                                @endif
                                <span class="text-gray-600 mt-1">{{ \Illuminate\Support\Carbon::parse($occurrence->date)->format('D, M j') }}</span>
                            </div>
                        </div>
                        <div class="justify-self-end">
                            <div class="flex justify-self-end gap-3 mb-2">
                                <div class=" font-semibold {{ $payment->direction === 'incoming' ? 'text-green-700' : 'text-red-700' }}">
                                    {{ formatBudgetAmount($payment->amount, $payment->direction) }}
                                </div>
                                
                                <span class="text-xs px-2 py-0.5 rounded {{ $payment->direction === 'incoming' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($payment->direction) }}
                                </span>
                            </div>
                        
                            <div class="status-buttons flex items-center justify-self-end gap-1">
                                @if($occurrence->status !== 'paid')
                                <form action="{{ route('budget.occurrences.mark-paid', $occurrence) }}" method="POST" onsubmit="return confirm('Mark this occurrence as paid?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs px-2 py-1 rounded bg-green-100 text-green-700 hover:bg-green-200">Mark Paid</button>
                                </form>
                                @endif
                                @if($occurrence->status !== 'pending')
                                <form action="{{ route('budget.occurrences.mark-unpaid', $occurrence) }}" method="POST" onsubmit="return confirm('Mark this occurrence as pending?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700 hover:bg-gray-200">Mark Pending</button>
                                </form>
                                @endif
                                @if($occurrence->status !== 'failed')
                                <form action="{{ route('budget.occurrences.mark-failed', $occurrence) }}" method="POST" onsubmit="return confirm('Mark this occurrence as failed?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs px-2 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200">Mark Failed</button>
                                </form>
                                @endif
                            </div>
                            
                            <div class="management-controls hidden flex items-center justify-self-end gap-1">
                                <button type="button" data-edit="{{ $payment->id }}" class="text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200">Edit</button>
                                <form action="{{ route('budget.payments.destroy', $payment) }}" method="POST" onsubmit="return confirm('Delete this payment? This removes all its occurrences.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs px-2 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Inline Edit Panel (used as template for modal) -->
                    <div id="edit-panel-{{ $payment->id }}" class="mt-3 hidden">
                        <form action="{{ route('budget.payments.update', $payment) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div>
                                    <label for="date-{{ $payment->id }}" class="block text-xs font-medium mb-1">Start Date</label>
                                    <input id="date-{{ $payment->id }}" name="date" type="date" value="{{ old('date', \Illuminate\Support\Carbon::parse($payment->date)->toDateString()) }}" class="w-full border rounded px-2 py-1">
                                </div>
                                <div>
                                    <label for="name-{{ $payment->id }}" class="block text-xs font-medium mb-1">Name</label>
                                    <input id="name-{{ $payment->id }}" name="name" type="text" value="{{ old('name', $payment->name) }}" class="w-full border rounded px-2 py-1">
                                </div>
                                <div>
                                    <label for="amount-{{ $payment->id }}" class="block text-xs font-medium mb-1">Amount</label>
                                    <input id="amount-{{ $payment->id }}" name="amount" type="number" step="0.01" value="{{ old('amount', $payment->amount) }}" class="w-full border rounded px-2 py-1">
                                </div>
                                <div>
                                    <label for="direction-{{ $payment->id }}" class="block text-xs font-medium mb-1">Direction</label>
                                    <select id="direction-{{ $payment->id }}" name="direction" class="w-full border rounded px-2 py-1">
                                        <option value="outgoing" {{ $payment->direction==='outgoing' ? 'selected' : '' }}>outgoing</option>
                                        <option value="incoming" {{ $payment->direction==='incoming' ? 'selected' : '' }}>incoming</option>
                                    </select>
                                </div>
                            </div>                            
                            <div class="md:col-span-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-center">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="repeatable" value="1" data-repeatable class="text-primary-600" {{ $payment->repeatable ? 'checked' : '' }}>
                                    <span class="text-sm">Repeatable</span>
                                </label>
                                <div data-repeat-fields class="{{ $payment->repeatable ? '' : 'opacity-50' }}">
                                    <label for="frequency-{{ $payment->id }}" class="block text-xs font-medium mb-1">Frequency</label>
                                    <select id="frequency-{{ $payment->id }}" name="frequency" class="w-full border rounded px-2 py-1" {{ $payment->repeatable ? '' : 'disabled' }}>
                                        <option value="">-- select --</option>
                                        <option value="weekly" {{ ($payment->frequency==='weekly') ? 'selected' : '' }}>weekly</option>
                                        <option value="monthly" {{ ($payment->frequency==='monthly') ? 'selected' : '' }}>monthly</option>
                                        <option value="yearly" {{ ($payment->frequency==='yearly') ? 'selected' : '' }}>yearly</option>
                                    </select>
                                </div>
                                <div data-repeat-fields class="{{ $payment->repeatable ? '' : 'opacity-50' }}">
                                    <label for="repeat_end_date-{{ $payment->id }}" class="block text-xs font-medium mb-1">Repeat Ends</label>
                                    <input id="repeat_end_date-{{ $payment->id }}" name="repeat_end_date" type="date" value="{{ old('repeat_end_date', optional($payment->repeat_end_date)->toDateString()) }}" class="w-full border rounded px-2 py-1" {{ $payment->repeatable ? '' : 'disabled' }}>
                                </div>
                            </div>
                            <div class="flex items-bottom justify-end gap-2">
                                <button type="button" data-cancel-edit="{{ $payment->id }}" class="px-3 py-1 rounded text-sm bg-gray-100 hover:bg-gray-200">Cancel</button>
                                <button type="submit" class="px-3 py-1 rounded text-sm bg-primary-600 text-white hover:bg-primary-700">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-sm text-gray-500">No payments in this pay period.</div>
        @endforelse
    </div>

    <!-- Edit Payment Modal -->
    <div id="editPaymentModal" class="hidden fixed inset-0 modal-background flex items-center justify-center p-4">
        <div class="bg-white rounded-lg w-full max-w-3xl p-6" id="editPaymentFormContainer"></div>
    </div>

    @push('scripts')
    <script>
        (function(){
            const toggleBtn = document.getElementById('add-payment-toggle');
            const panel = document.getElementById('add-payment-panel');
            const managementToggle = document.getElementById('management-mode-toggle');
            if (!toggleBtn || !panel) return;

            toggleBtn.addEventListener('click', function() {
                panel.classList.toggle('hidden');
            });

            // Management mode toggle
            let managementMode = localStorage.getItem('managementMode') === 'true';
            
            function updateManagementUI() {
                managementToggle.textContent = managementMode ? 'Done?' : 'Manage Payments';
                
                // Toggle Add button visibility
                const addBtn = document.getElementById('add-payment-toggle');
                if (managementMode) {
                    addBtn.classList.remove('hidden');
                } else {
                    addBtn.classList.add('hidden');
                    // Also hide the add panel if it's open
                    document.getElementById('add-payment-panel').classList.add('hidden');
                }
                
                // Toggle visibility of controls
                document.querySelectorAll('.budget-item').forEach(item => {
                    const managementControls = item.querySelector('.management-controls');
                    const statusControls = item.querySelector('.status-controls');
                    const statusButtons = item.querySelector('.status-buttons');
                    
                    if (managementMode) {
                        managementControls.classList.remove('hidden');
                        statusControls.classList.add('hidden');
                        statusButtons.classList.add('hidden');
                    } else {
                        managementControls.classList.add('hidden');
                        statusControls.classList.remove('hidden');
                        statusButtons.classList.remove('hidden');
                    }
                });
            }
            
            // Initialize UI on page load
            updateManagementUI();
            
            managementToggle.addEventListener('click', function() {
                managementMode = !managementMode;
                localStorage.setItem('managementMode', managementMode);
                updateManagementUI();
            });

            const repeatable = document.getElementById('repeatable');
            const repeatFields = document.getElementsByClassName('repeat-fields');
            if (repeatable && repeatFields) {
                const controls = [];
                Array.from(repeatFields).forEach(el => {
                    controls.push(...el.querySelectorAll('select, input'));
                });
                const applyRepeat = () => {
                    const enabled = repeatable.checked;
                    Array.from(repeatFields).forEach(el => {
                        el.classList.toggle('opacity-50', !enabled);
                    });
                    controls.forEach(el => el.disabled = !enabled);
                };
                repeatable.addEventListener('change', applyRepeat);
                applyRepeat();
            }
            const addClose = document.getElementById('add-payment-close');
            addClose && addClose.addEventListener('click', ()=> panel.classList.add('hidden'));
        })();
        (function(){
            const toast = document.getElementById('toast');
            if (!toast) return;
            const close = document.getElementById('toast-close');
            const hide = ()=> toast.remove();
            close && close.addEventListener('click', hide);
            setTimeout(hide, 3500);
        })();
        // Inline edit toggles
        (function(){
            const modal = document.getElementById('editPaymentModal');
            const container = document.getElementById('editPaymentFormContainer');
            function openEdit(paymentId){
                const tmpl = document.getElementById(`edit-panel-${paymentId}`);
                if (!tmpl) return;
                container.innerHTML = tmpl.innerHTML;
                // Wire cancel in injected form
                const cancelBtn = container.querySelector('[data-cancel-edit]');
                cancelBtn && cancelBtn.addEventListener('click', ()=> modal.classList.add('hidden'));
                // Wire repeatable toggle within injected form
                const repeatableEl = container.querySelector('[data-repeatable]');
                const repeatFieldsEls = container.querySelectorAll('[data-repeat-fields]');
                if (repeatableEl && repeatFieldsEls.length){
                    const applyRepeatState = ()=> {
                        const enabled = repeatableEl.checked;
                        repeatFieldsEls.forEach(group => {
                            group.classList.toggle('opacity-50', !enabled);
                            group.querySelectorAll('select, input').forEach(el => el.disabled = !enabled);
                        });
                    };
                    repeatableEl.addEventListener('change', applyRepeatState);
                    applyRepeatState();
                }
                modal.classList.remove('hidden');
            }
            // Delegate edit buttons
            document.addEventListener('click', function(e){
                const btn = e.target.closest('[data-edit]');
                if (btn){
                    e.preventDefault();
                    openEdit(btn.getAttribute('data-edit'));
                }
                if (e.target === modal){ modal.classList.add('hidden'); }
            });
            // Escape closes overlays
            document.addEventListener('keydown', function(e){
                if (e.key === 'Escape'){
                    modal.classList.add('hidden');
                    const panel = document.getElementById('add-payment-panel');
                    panel && panel.classList.add('hidden');
                }
        });
        document.querySelectorAll('[data-cancel-edit]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-cancel-edit');
                const panel = document.getElementById(`edit-panel-${id}`);
                if (panel) panel.classList.add('hidden');
            });
        });
    })();
</script>
@endpush
@endsection
