<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PlannerController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Display the planner for a specific date
     */
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $date = Carbon::parse($date);
        
        $todos = Todo::forUser(Auth::id())
            ->forDate($date)
            ->orderBy('due_date')
            ->get();

        return view('planner.index', [
            'date' => $date,
            'todos' => $todos,
            'previousDate' => $date->copy()->subDay()->toDateString(),
            'nextDate' => $date->copy()->addDay()->toDateString(),
            'today' => now()->toDateString(),
        ]);
    }

    /**
     * Store a newly created todo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'type' => 'required|in:one_time,recurring,habit',
            'frequency' => 'required_if:type,recurring,habit',
            'recurrence_ends_at' => 'nullable|date|after_or_equal:due_date',
            'target_count' => 'required_if:type,habit|integer|min:1',
            'is_skippable' => 'boolean',
        ]);

        $todoData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'],
            'completed' => false,
            'type' => $validated['type'],
        ];

        // Handle recurring task fields
        if ($validated['type'] === 'recurring') {
            $todoData['frequency'] = $validated['frequency'];
            if (!empty($validated['recurrence_ends_at'])) {
                $todoData['recurrence_ends_at'] = $validated['recurrence_ends_at'];
            }
        }

        // Handle habit fields
        if ($validated['type'] === 'habit') {
            $todoData['frequency'] = $validated['frequency'];
            $todoData['target_count'] = $validated['target_count'];
            $todoData['is_skippable'] = $request->has('is_skippable');
            $todoData['current_streak'] = 0;
            $todoData['longest_streak'] = 0;
        }

        $todo = Auth::user()->todos()->create($todoData);

        return redirect()
            ->route('planner.index', ['date' => Carbon::parse($validated['due_date'])->toDateString()])
            ->with('success', 'Task created successfully!');
    }

    /**
     * Toggle todo completion status
     */
    /**
     * Show the form for editing the specified todo
     */
    public function edit(Todo $todo)
    {
        $this->authorize('update', $todo);
        
        return view('planner._edit_form', [
            'todo' => $todo
        ]);
    }

    /**
     * Toggle todo completion status
     */
    public function toggleComplete(Todo $todo, Request $request)
    {
        $this->authorize('update', $todo);
        
        $date = $request->input('date', now()->toDateString());
        
        if ($todo->is_habit) {
            // For habits, increment/decrement the completion count
            $completion = $todo->getCompletionForDate($date);
            
            if ($completion && $completion->count > 0) {
                // If already completed, decrement the count
                $todo->decrementCompletion($date);
                $message = 'Habit unchecked for ' . Carbon::parse($date)->format('M j, Y');
            } else {
                // If not completed, increment the count (up to target)
                $todo->incrementCompletion($date);
                $message = 'Habit checked for ' . Carbon::parse($date)->format('M j, Y');
            }
            
            // Update the completion status based on the count
            $completion = $todo->getCompletionForDate($date);
            $completed = $completion && $completion->count >= $todo->target_count;
        } else {
            // For regular todos, just toggle the completed status
            $completed = !$todo->completed;
            $todo->update(['completed' => $completed]);
            $message = $completed ? 'Task completed!' : 'Task marked as incomplete';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'completed' => $completed,
                'completion_count' => $todo->is_habit ? $todo->getCompletionCountAttribute() : null,
                'target_count' => $todo->is_habit ? $todo->target_count : null
            ]);
        }

        return redirect()
            ->route('planner.index', ['date' => $date])
            ->with('success', $message);
    }

    /**
     * Update the specified todo
     */
    public function update(Request $request, Todo $todo)
    {
        $this->authorize('update', $todo);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'completed' => 'boolean',
            'type' => 'required|in:one_time,recurring,habit',
            'frequency' => 'required_if:type,recurring,habit',
            'recurrence_ends_at' => 'nullable|date|after_or_equal:due_date',
            'target_count' => 'required_if:type,habit|integer|min:1',
            'is_skippable' => 'boolean',
        ]);

        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'],
            'type' => $validated['type'],
        ];

        // Handle completed status
        if ($request->has('completed')) {
            $updateData['completed'] = $validated['completed'];
        }

        // Handle recurring task fields
        if ($validated['type'] === 'recurring') {
            $updateData['frequency'] = $validated['frequency'];
            $updateData['recurrence_ends_at'] = $validated['recurrence_ends_at'] ?? null;
            
            // Clear habit-specific fields if type changed from habit
            $updateData['target_count'] = null;
            $updateData['current_streak'] = null;
            $updateData['longest_streak'] = null;
            $updateData['is_skippable'] = true;
        }
        // Handle habit fields
        elseif ($validated['type'] === 'habit') {
            $updateData['frequency'] = $validated['frequency'];
            $updateData['target_count'] = $validated['target_count'];
            $updateData['is_skippable'] = $request->has('is_skippable');
            
            // Clear recurring-specific fields if type changed from recurring
            $updateData['recurrence_ends_at'] = null;
            
            // Initialize streaks if this is a new habit
            if ($todo->type !== 'habit') {
                $updateData['current_streak'] = 0;
                $updateData['longest_streak'] = 0;
            }
        }
        // Handle one-time task
        else {
            // Clear all special fields
            $updateData['frequency'] = null;
            $updateData['recurrence_ends_at'] = null;
            $updateData['target_count'] = null;
            $updateData['current_streak'] = null;
            $updateData['longest_streak'] = null;
            $updateData['is_skippable'] = true;
        }

        $todo->update($updateData);

        return redirect()
            ->route('planner.index', ['date' => Carbon::parse($validated['due_date'])->toDateString()])
            ->with('success', 'Task updated successfully!');
    }

    /**
     * Remove the specified todo
     */
    public function destroy(Todo $todo)
    {
        $this->authorize('delete', $todo);
        
        $todo->delete();

        return back()->with('success', 'Todo deleted!');
    }
}
