<form id="editTodoForm" action="{{ route('planner.update', $todo) }}" method="POST" class="space-y-4">
    @csrf
    @method('PUT')
    
    <div>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
        <input type="text" 
               id="title"
               name="title" 
               value="{{ old('title', $todo->title) }}" 
               placeholder="Task title"
               class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent"
               required>
    </div>
    
    <!-- <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea id="description"
                  name="description" 
                  placeholder="Add details (optional)"
                  class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent">{{ old('description', $todo->description) }}</textarea>
    </div> -->
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Task Type</label>
            <select id="type" 
                    name="type" 
                    class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    onchange="toggleTaskTypeFields(this.value)">
                <option value="one_time" {{ $todo->type === 'one_time' ? 'selected' : '' }}>One-time Task</option>
                <option value="recurring" {{ $todo->type === 'recurring' ? 'selected' : '' }}>Recurring Task</option>
                <option value="habit" {{ $todo->type === 'habit' ? 'selected' : '' }}>Habit</option>
            </select>
        </div>
        
        <div>
            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date & Time</label>
            <input type="datetime-local" 
                   id="due_date"
                   name="due_date" 
                   value="{{ old('due_date', $todo->due_date->setTimezone(auth()->user()->timezone)->format('Y-m-d\TH:i')) }}" 
                   class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                   required>
        </div>
    </div>
    
    <!-- Recurring Task Fields -->
    <div id="recurringFields" style="display: {{ $todo->type === 'recurring' ? 'block' : 'none' }};" class="space-y-4 p-4 bg-gray-50 rounded-lg">
        <h3 class="font-medium text-gray-700">Recurrence Settings</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="recurring_frequency" class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                <select id="recurring_frequency" 
                        name="recurring_frequency" 
                        class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="daily" {{ (old('recurring_frequency', $todo->frequency ?? '') === 'daily') ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ (old('recurring_frequency', $todo->frequency ?? '') === 'weekly') ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ (old('recurring_frequency', $todo->frequency ?? '') === 'monthly') ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>
            
            <div>
                <label for="recurrence_ends_at" class="block text-sm font-medium text-gray-700 mb-1">Ends On (optional)</label>
                <input type="date" 
                       id="recurrence_ends_at"
                       name="recurrence_ends_at" 
                       value="{{ old('recurrence_ends_at', $todo->recurrence_ends_at ? $todo->recurrence_ends_at->format('Y-m-d') : '') }}" 
                       class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
        </div>
    </div>
    
    <!-- Habit Fields -->
    <div id="habitFields" style="display: {{ $todo->type === 'habit' ? 'block' : 'none' }};" class="space-y-4 p-4 bg-gray-50 rounded-lg">
        <h3 class="font-medium text-gray-700">Habit Settings</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="habit_frequency" class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                <select id="habit_frequency" 
                        name="frequency" 
                        class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="daily" {{ (old('frequency', $todo->frequency ?? 'daily') === 'daily') ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ (old('frequency', $todo->frequency ?? '') === 'weekly') ? 'selected' : '' }}>Weekly</option>
                </select>
            </div>
            
            <div>
                <label for="target_count" class="block text-sm font-medium text-gray-700 mb-1">Target (per day/week)</label>
                <input type="number" 
                       id="target_count"
                       name="target_count" 
                       min="1"
                       value="{{ old('target_count', $todo->target_count ?? 1) }}" 
                       class="w-full p-2 border rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
        </div>
        
        <div>
            <div class="flex items-center space-x-2">
                <input type="checkbox" 
                       id="is_skippable" 
                       name="is_skippable" 
                       value="1"
                       {{ old('is_skippable', $todo->is_skippable ?? true) ? 'checked' : '' }}
                       class="h-4 w-4 text-primary-600 rounded focus:ring-primary-500">
                <label for="is_skippable" class="text-sm text-gray-700">Allow skipping this habit</label>
            </div>
        </div>
    </div>
    
    <div class="flex items-center justify-between pt-2">
        <div class="flex items-center space-x-2">
            <input type="checkbox" 
                   id="completed" 
                   name="completed" 
                   value="1"
                   {{ $todo->completed ? 'checked' : '' }}
                   class="h-4 w-4 text-primary-600 rounded focus:ring-primary-500">
            <label for="completed" class="text-sm font-medium text-gray-700">Mark as completed</label>
        </div>
        
        <div class="flex space-x-3">
            <button type="button" 
                    onclick="document.getElementById('editTodoModal').classList.add('hidden')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Cancel
            </button>
            <button type="submit" 
                    class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Save Changes
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    function toggleTaskTypeFields(type) {
        document.getElementById('recurringFields').style.display = type === 'recurring' ? 'block' : 'none';
        document.getElementById('habitFields').style.display = type === 'habit' ? 'block' : 'none';
    }
    
    // Form submission handler
    document.getElementById('editTodoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {};
        
        // Convert FormData to plain object
        formData.forEach((value, key) => {
            // Handle checkboxes
            if (key === 'is_skippable') {
                data[key] = value === 'on' ? 1 : 0;
            } else {
                data[key] = value;
            }
        });
        
        // Add _method for Laravel's method spoofing
        data['_method'] = 'PUT';
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>
@endpush
