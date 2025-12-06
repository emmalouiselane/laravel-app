@extends('layouts.app')

@section('title', 'Daily Planner')

@push('styles')
<style>
    .planner-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem;
    }
    .planner-header {
        align-items: center;
        margin: 1rem auto;
    }

    .date-navigation {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 200px;
        justify-content: center;
    }
    
    .date-display {
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .todo-item {
        display: flex;
        align-items: flex-start;
        padding: 1rem;
        background: white;
    }
    
    .todo-item.completed .todo-title {
        opacity: 0.7;
        color: gray;
        text-decoration: line-through;
    }
    
    .todo-checkbox {
        margin-right: 1rem;
        margin-top: 0.25rem;
    }
    
    .todo-content {
        flex: 1;
    }
    
    .todo-title {
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    
    .todo-description {
        color: #6b7280;
        font-size: 0.875rem;
    }
    
    .todo-time {
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    .todo-actions {
        margin: auto;
    }
    
    .add-todo-form {
        background: white;
        border-radius: 0.5rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="planner-container">
    <header class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Daily Planner</h1>

        <x-auth-buttons 
            :showDashboard="true"
            :showAccount="true"
            :showLogout="true"
        />
    </header>
    
    <div class="planner-header grid grid-cols-3">
        <a href="{{ route('planner.index') }}" 
            style="width: 60px;"
            class="px-3 py-1 rounded text-sm {{ $date->toDateString() === $today ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-primary-100 text-primary-600 hover:bg-primary-200' }}"
            {{ $date->toDateString() === $today ? 'disabled' : '' }}>
            Today
        </a>

        <div class="date-navigation flex items-center">
            <a href="{{ route('planner.index', ['date' => $previousDate]) }}" style="width: 30px; height: 28px;" 
                class="px-2 bg-gray-100 rounded hover:bg-gray-200">
                <x-bladewind::icon name="arrow-left" class="size-4" />
            </a>
            <span class="date-display">
                {{ $date->format('j M Y') }}
            </span>
            <a href="{{ route('planner.index', ['date' => $nextDate]) }}" style="width: 30px; height: 28px;" 
                class="px-2 bg-gray-100 rounded hover:bg-gray-200">
                <x-bladewind::icon name="arrow-right" class="size-4" />
            </a>
        </div>

        <!-- Add Todo Form Toggle Button -->
        <div class="flex justify-end items-center gap-2">
            <button type="button" onclick="toggleAddForm()" 
                    style="width: 60px;" class="px-3 py-1 rounded text-sm bg-accent text-white hover:opacity-90">
                Add
            </button>
        </div>
    </div>
    
    <!-- Add Todo Form (initially hidden) -->
    <div id="addTodoFormContainer" class="hidden mb-6 absolute z-10 right-8">
        <div class="add-todo-form bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Add New Task</h3>
                <button type="button" 
                        onclick="toggleAddForm()" 
                        class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('planner.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" 
                           id="title"
                           name="title" 
                           placeholder="What needs to be done?" 
                           class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           required>
                </div>
            
                <!-- <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                    <textarea id="description"
                            name="description" 
                            placeholder="Add details"
                            class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
                </div> -->
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Task Type</label>
                        <select id="type" 
                                name="type" 
                                class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                onchange="toggleTaskTypeFields(this.value, 'add')">
                            <option value="one_time">One-time Task</option>
                            <option value="recurring">Recurring Task</option>
                            <option value="habit">Habit</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date & Time</label>
                        <input type="datetime-local" 
                            id="due_date"
                            name="due_date" 
                            value="{{ $date->format('Y-m-d\TH:i') }}" 
                            class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            required>
                    </div>
                </div>
                
                <!-- Recurring Task Fields -->
                <div id="addRecurringFields" style="display: none;" class="space-y-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-medium text-gray-700">Recurrence Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="frequency" class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                            <select id="recurring_frequency" 
                                    name="recurring_frequency" 
                                    class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="recurrence_ends_at" class="block text-sm font-medium text-gray-700 mb-1">Ends On (optional)</label>
                            <input type="date" 
                                id="recurrence_ends_at"
                                name="recurrence_ends_at" 
                                class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
                
                <!-- Habit Fields -->
                <div id="addHabitFields" style="display: none;" class="space-y-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-medium text-gray-700">Habit Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="habit_frequency" class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                            <select id="habit_frequency" 
                                    name="frequency" 
                                    class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="target_count" class="block text-sm font-medium text-gray-700 mb-1">Target (per day/week)</label>
                            <input type="number" 
                                id="target_count"
                                name="target_count" 
                                min="1"
                                value="1"
                                class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" 
                                id="is_skippable" 
                                name="is_skippable" 
                                value="1"
                                checked
                                class="h-4 w-4 text-primary-600 rounded focus:ring-primary-500">
                            <label for="is_skippable" class="text-sm text-gray-700">Allow skipping this habit</label>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end pt-2">
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Add Task
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Todo List -->
    <div class="todo-list">
        @php
            // Group todos by type
            $groupedTodos = [
                'habits' => $todos->where('is_habit', true),
                'recurring' => $todos->where('is_recurring', true)->where('is_habit', false),
                'one_time' => $todos->where('is_habit', false)->where('is_recurring', false)
            ];
            
            $sectionTitles = [
                'habits' => 'Habits',
                'one_time' => 'One-time Tasks',
                'recurring' => 'Recurring Tasks'
            ];
        @endphp
        
        @foreach($groupedTodos as $type => $todosGroup)
            <div class="border rounded-lg overflow-hidden mb-4">
                <button type="button" 
                        onclick="toggleAccordion(this, '{{ $type }}')" 
                        class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 text-left font-medium flex justify-between items-center focus:outline-none">
                    <span>{{ $sectionTitles[$type] }} ({{ $todosGroup->count() }})</span>
                    <x-bladewind::icon name="chevron-up" class="size-5 transition-transform duration-200 accordion-icon" data-type="{{ $type }}" />
                </button>
                
                <div id="{{ $type }}-content" class="divide-y">
                    @foreach($todosGroup as $todo)
                        @php
                            $completionCount = $todo->is_habit ? $todo->getCompletionCount($date) : 0;
                            $isCompleted = $todo->is_habit ? ($completionCount === $todo->target_count) : $todo->completed;
                        @endphp
                        <div class="todo-item {{ $isCompleted ? 'completed' : '' }}" data-todo-id="{{ $todo->id }}">
                            <form action="{{ route('planner.toggle-complete', $todo) }}" method="POST" class="todo-checkbox m-auto" onsubmit="toggleTodoComplete(event, {{ $todo->id }})">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                @if($todo->is_habit && $todo->target_count > 1)
                                    <div class="flex items-center space-x-1 m-auto">
                                        <button type="button" 
                                            onclick="updateHabitCount(event, {{ $todo->id }}, -1, '{{ $date->toDateString() }}')"
                                            class="decrement-btn h-6 w-6 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                            {{ $completionCount <= 0 ? 'disabled' : '' }}>
                                            -
                                        </button>
                                                            
                                        <button type="button"
                                            onclick="updateHabitCount(event, {{ $todo->id }}, 1, '{{ $date->toDateString() }}')"
                                            class="increment-btn h-6 w-6 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                            {{ $completionCount >= $todo->target_count ? 'disabled' : '' }}>
                                            +
                                        </button>
                                    </div>
                                @else
                                    <input type="hidden" name="change" :value="this.checked ? 1 : -1">
                                    <input type="checkbox" 
                                        {{ ($todo->is_habit ? $completionCount > 0 : $todo->completed) ? 'checked' : '' }}
                                        onchange="this.previousElementSibling.value = this.checked ? 1 : -1; this.form.submit()"
                                        class="h-5 w-5 text-primary-600 rounded focus:ring-primary-500 cursor-pointer">
                                @endif
                            </form>
                            <div class="todo-content ml-5">                        
                                @if($todo->is_habit)
                                    <div class="flex items-center space-x-2 {{ $todo->recurrence_ends_at ? '' : 'mt-2' }}">
                                        <span class="todo-title my-auto">{{ $todo->title }}</span>

                                        @php
                                            // Calculate streak as of the selected date
                                            $streak = $todo->calculateStreakForDate($date);
                                        @endphp
                                        
                                        @if($streak > 0)
                                            <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-800 rounded-full">
                                                🔥 {{ $streak }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    @if($todo->is_recurring)
                                        <div class="flex items-center space-x-2 {{ $todo->recurrence_ends_at ? '' : 'mt-2' }}">
                                            <span class="todo-title my-auto">{{ $todo->title }}</span>
                                            <span class="text-xs px-2 py-0.5 bg-primary-100 text-primary-800 rounded-full">
                                                {{ ucfirst($todo->frequency) }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex items-center space-x-2 {{ $todo->recurrence_ends_at ? '' : 'mt-2' }}">
                                            <span class="todo-title my-auto">{{ $todo->title }}</span>
                                        </div>
                                    @endif
                                @endif
                    
                                <div class="flex items-center space-x-4 mt-1">
                                    @php
                                        $userTime = $todo->due_date->setTimezone(auth()->user()->timezone);
                                    @endphp
                                    @if(!$userTime->isMidnight())
                                        <div class="todo-time text-sm text-gray-500">
                                            {{ $userTime->format('g:i A') }}
                                        </div>
                                    @endif
                                    
                                    @if($todo->is_habit && $todo->target_count > 1)
                                        <div class="flex items-center space-x-1">
                                            @for($i = 0; $i < $todo->target_count; $i++)
                                                <div class="progress-dot w-2 h-2 rounded-full {{ $i < $completionCount ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                                            @endfor
                                            <span class="habit-count text-xs text-gray-500 ml-1">
                                                ({{ $completionCount }}/{{ $todo->target_count }})
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if($todo->is_recurring && $todo->recurrence_ends_at)
                                        <div class="text-xs text-gray-500">
                                            Ends {{ $todo->recurrence_ends_at->format('M j, Y') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="todo-actions">
                                <x-bladewind::button.circle icon="pencil" outline="true" size="tiny" color="gray" onclick="editTodo({{ $todo->id }})" />
                                
                                <form action="{{ route('planner.destroy', $todo) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-bladewind::button.circle icon="trash" outline="true" size="tiny" can_submit="true" color="gray" />
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Edit Todo Modal -->
<div id="editTodoModal" class="hidden fixed inset-0 modal-background flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-md p-6" id="editTodoFormContainer">
        <!-- Will be populated by JavaScript -->
    </div>
</div>

@push('scripts')
<script>
    // Toggle accordion sections
    function toggleAccordion(button, type) {
        const content = document.getElementById(`${type}-content`);
        const icon = button.querySelector('.accordion-icon');
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.style.transform = 'rotate(0deg)';
        } else {
            content.classList.add('hidden');
            icon.style.transform = 'rotate(-180deg)';
        }
    }
    
    // Function to toggle task type specific fields in the add form
    function toggleTaskTypeFields(type, formType = 'add') {
        const prefix = formType === 'add' ? 'add' : 'edit';
        
        // Hide all field groups first
        document.getElementById(`${prefix}RecurringFields`).style.display = 'none';
        document.getElementById(`${prefix}HabitFields`).style.display = 'none';
        
        // Show the appropriate field group based on the selected type
        if (type === 'recurring') {
            document.getElementById(`${prefix}RecurringFields`).style.display = 'block';
        } else if (type === 'habit') {
            document.getElementById(`${prefix}HabitFields`).style.display = 'block';
        }
    }

    // Initialize the form fields when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial state for add form
        const initialType = document.getElementById('type').value;
        toggleTaskTypeFields(initialType, 'add');
        
        // Set initial state for edit form when it's loaded
        const editModal = document.getElementById('editTodoModal');
        if (editModal) {
            const observer = new MutationObserver(function(mutations) {
                const editType = document.getElementById('edit_type');
                if (editType) {
                    // Set initial state for edit form
                    toggleTaskTypeFields(editType.value, 'edit');
                    
                    // Add event listener for type changes in edit form
                    editType.addEventListener('change', function() {
                        toggleTaskTypeFields(this.value, 'edit');
                    });
                }
            });
            
            observer.observe(editModal, { childList: true, subtree: true });
        }
    });

    function editTodo(todoId) {
        fetch(`/planner/${todoId}/edit`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editTodoFormContainer').innerHTML = html;
                document.getElementById('editTodoModal').classList.remove('hidden');
                
                // Initialize the edit form fields
                const editType = document.getElementById('edit_type');
                if (editType) {
                    toggleTaskTypeFields(editType.value, 'edit');
                    
                    // Add event listener for type changes in edit form
                    editType.addEventListener('change', function() {
                        toggleTaskTypeFields(this.value, 'edit');
                    });
                }
            });
    }
    
    // Close modal when clicking outside
    document.getElementById('editTodoModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
    
    // Close modal when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('editTodoModal').classList.add('hidden');
            const addForm = document.getElementById('addTodoFormContainer');
            if (!addForm.classList.contains('hidden')) {
                addForm.classList.add('hidden');
            }
        }
    });
    
    // Update habit completion count
    async function updateHabitCount(event, todoId, change, date) {
        event.preventDefault();
        
        const form = event.target.closest('form');
        const formData = new FormData(form);
        const url = form.action;
        
        try {
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': formData.get('_token'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    _token: formData.get('_token'),
                    _method: 'PATCH',
                    date: date,
                    change: change
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update the UI
                const todoItem = event.target.closest('.todo-item');
                const countElement = todoItem.querySelectorAll('.habit-count');
                const progressDots = todoItem.querySelectorAll('.progress-dot');
                const incrementBtn = todoItem.querySelector('.increment-btn');
                const decrementBtn = todoItem.querySelector('.decrement-btn');
                
                // Update count display
                countElement.forEach((element) => {
                    element.textContent = data.completion_count + '/' + data.target_count;
                });
                
                // Update progress dots
                if (progressDots.length > 0) {
                    progressDots.forEach((dot, index) => {
                        if (index < data.completion_count) {
                            dot.classList.add('bg-green-500');
                            dot.classList.remove('bg-gray-200');
                        } else {
                            dot.classList.remove('bg-green-500');
                            dot.classList.add('bg-gray-200');
                        }
                    });
                }
                
                // Update button states
                if (incrementBtn) {
                    incrementBtn.disabled = data.completion_count >= data.target_count;
                }
                if (decrementBtn) {
                    decrementBtn.disabled = data.completion_count <= 0;
                }
                
                // Update completion status
                if (data.completion_count >= data.target_count) {
                    todoItem.classList.add('completed');
                } else {
                    todoItem.classList.remove('completed');
                }
            } else {
                alert('Error: ' + (data.message || 'Failed to update habit'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating the habit');
        }
    }
    
    // Toggle add form visibility
    function toggleAddForm() {
        const formContainer = document.getElementById('addTodoFormContainer');
        formContainer.classList.toggle('hidden');
        
        // Scroll to form when opening
        if (!formContainer.classList.contains('hidden')) {
            formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.getElementById('title').focus();
        }
    }
</script>
@endpush
@endsection
