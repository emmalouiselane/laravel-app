@extends('layouts.app')

@section('title', 'Budget Settings')

@push('styles')
<style>
    .settings-container { max-width: 600px; margin: 0 auto; padding: 1rem; }
    .card { background: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid #eee; }
    .card-body { padding: 1.25rem; }
    .card-footer { padding: 1rem 1.25rem; border-top: 1px solid #eee; }
    .label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
    .input { width: 100%; border: 1px solid #ddd; border-radius: 0.375rem; padding: 0.5rem 0.75rem; }
</style>
@endpush

@section('content')
<div class="settings-container">
    <header class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Budget Settings</h1>
        <a href="{{ route('budget.index') }}" class="px-3 py-1 rounded text-sm bg-gray-100 text-gray-700 hover:bg-gray-200">Back to Budget</a>
    </header>

    @if (session('success'))
        <div class="mb-4 p-3 rounded bg-green-50 text-green-700">{{ session('success') }}</div>
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

    <div class="card">
        <div class="card-header">
            <h2 class="font-semibold">Pay Period</h2>
            <p class="text-sm text-gray-500">Choose weekly or monthly pay periods. For monthly, select the start day of the month.</p>
        </div>
        <form action="{{ route('budget.settings.update') }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="card-body space-y-4">
                <div>
                    <label for="pay_period_mode" class="label">Pay period mode</label>
                    <select id="pay_period_mode" name="pay_period_mode" class="input max-w-40">
                        <option value="monthly" {{ ($mode ?? 'monthly')==='monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="weekly" {{ ($mode ?? 'monthly')==='weekly' ? 'selected' : '' }}>Weekly</option>
                    </select>
                </div>
                <div id="monthly_fields" class="{{ ($mode ?? 'monthly')==='monthly' ? '' : 'hidden' }}">
                    <label for="pay_period_start_day" class="label">Pay period start day</label>
                    <select id="pay_period_start_day" name="pay_period_start_day" class="input max-w-40">
                        @for ($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}" {{ (int)$startDay === $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="card-footer flex justify-end gap-2">
                <a href="{{ route('budget.index') }}" class="px-3 py-1 rounded text-sm bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</a>
                <button type="submit" class="px-3 py-1 rounded text-sm bg-primary-600 text-white hover:bg-primary-700">Save</button>
            </div>
        </form>
    </div>
@push('scripts')
<script>
    (function(){
        const modeSel = document.getElementById('pay_period_mode');
        const monthly = document.getElementById('monthly_fields');
        if (modeSel && monthly) {
            modeSel.addEventListener('change', function(){
                monthly.classList.toggle('hidden', this.value !== 'monthly');
            });
        }
    })();
</script>
@endpush
</div>
@endsection
