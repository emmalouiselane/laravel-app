@extends('layouts.app')

@section('title', 'Budget')

@push('styles')
<style>
    .budget-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem;
    }
    .budget-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 1rem auto;
        max-width: 400px;
    }
    
    .date-display {
        font-size: 0.875rem;
        font-weight: 600;
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
    
    /* .add-todo-form {
        background: white;
        border-radius: 0.5rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    } */
</style>
@endpush

@section('content')
<div class="budget-container">
    <header class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Budget</h1>

        <x-auth-buttons 
            :showDashboard="true"
            :showAccount="true"
            :showLogout="true"
        />
    </header>
    
    <div class="budget-header">
        <a href="{{ route('budget.index') }}" 
            style="width: 90px;"
            class="px-3 py-1 rounded text-sm {{ $date->format('m-Y') === $today->format('m-Y') ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-primary-100 text-primary-600 hover:bg-primary-200' }}"
            {{ $date->format('m-Y') === $today->format('m-Y') ? 'disabled' : '' }}>
            This Month
        </a>
        

        <div class="date-navigation">
            <a href="{{ route('budget.index', ['date' => $previousMonth]) }}" style="width: 30px; height: 28px;" 
                class="px-2 bg-gray-100 rounded hover:bg-gray-200">
                <x-bladewind::icon name="arrow-left" class="size-4" />
            </a>
            <span class="date-display">
                {{ $date->format('F Y') }}
            </span>
            <a href="{{ route('budget.index', ['date' => $nextMonth]) }}" style="width: 30px; height: 28px;" 
                class="px-2 bg-gray-100 rounded hover:bg-gray-200">
                <x-bladewind::icon name="arrow-right" class="size-4" />
            </a>
        </div>

        <!-- Add Budget Form Toggle Button -->
        <button type="button" disabled
                style="width: 60px;" class="px-3 py-1 rounded text-sm bg-primary-100 text-primary-600 hover:bg-primary-200">
            Add
        </button>
    </div>
    
 
    <!-- Budget List -->
    <div class="budget-list">
      ** IN DEVELOPMENT **
    </div>
</div>

@push('scripts')
<script>
   
</script>
@endpush
@endsection
