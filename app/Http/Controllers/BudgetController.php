<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\Payment;
use App\Models\PaymentOccurrence;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BudgetController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Display the budget for this pay period
     */
    /**
     * Calculate pay period dates based on a given date
     */
    protected function calculatePayPeriod($date = null)
    {
        $date = $date ? Carbon::parse($date) : now();

        // Read settings from DB if table exists; defaults
        $startDay = 28;
        $mode = 'monthly'; // 'monthly' or 'weekly'
        if (Schema::hasTable('budget_settings')) {
            $userId = Auth::id();
            $row = null;
            if ($userId) {
                $row = DB::table('budget_settings')->where('user_id', $userId)->first();
            }
            // Fallback to legacy global row id=1 if no user-specific row exists
            if (!$row) {
                $row = DB::table('budget_settings')->where('id', 1)->first();
            }
            if ($row) {
                if (is_numeric($row->pay_period_start_day) && $row->pay_period_start_day >= 1 && $row->pay_period_start_day <= 31) {
                    $startDay = (int) $row->pay_period_start_day;
                }
                if (!empty($row->pay_period_mode)) {
                    $mode = in_array($row->pay_period_mode, ['monthly','weekly']) ? $row->pay_period_mode : 'monthly';
                }
            }
        }
        if ($mode === 'weekly') {
            // Weekly period: Monday..Sunday (Carbon default startOfWeek is Monday)
            $periodStart = $date->copy()->startOfWeek();
            $periodEnd = $date->copy()->endOfWeek();
            $previousStart = $periodStart->copy()->subWeek()->toDateString();
            $nextPeriod = $periodStart->copy()->addWeek()->toDateString();
        } else {
            // Monthly period: from configured start day to day before next month same day
            $safeDayForMonth = function (Carbon $d) use ($startDay) {
                return min($startDay, $d->daysInMonth);
            };

            // Candidate start in the same month as $date
            $candidateStart = $date->copy()->day($safeDayForMonth($date))->startOfDay();

            // If current date is before the candidate start, the period started last month
            if ($date->lt($candidateStart)) {
                $prev = $date->copy()->subMonthNoOverflow();
                $periodStart = $prev->copy()->day($safeDayForMonth($prev))->startOfDay();
            } else {
                $periodStart = $candidateStart;
            }

            // Next period start is one month after periodStart, same start day (safe)
            $nextStartMonth = $periodStart->copy()->addMonthNoOverflow();
            $nextStart = $nextStartMonth->copy()->day($safeDayForMonth($nextStartMonth))->startOfDay();

            // Previous period start is one month before periodStart, same start day (safe)
            $prevStartMonth = $periodStart->copy()->subMonthNoOverflow();
            $previousStart = $prevStartMonth->copy()->day($safeDayForMonth($prevStartMonth))->startOfDay()->toDateString();

            // Period ends the day before the next start
            $periodEnd = $nextStart->copy()->subDay()->endOfDay();
            $nextPeriod = $nextStart->toDateString();
        }

        $today = now();
        // Use inclusive between to consider boundary dates as current
        $isCurrent = $today->between($periodStart, $periodEnd, true);

        return [
            'start_date' => $periodStart,
            'end_date' => $periodEnd,
            'previous_period' => $previousStart,
            'next_period' => $nextPeriod,
            'is_current' => $isCurrent,
            'start_day' => $startDay,
        ];
    }
    
    /**
     * Display the budget for the current pay period
     */
    public function index(Request $request)
    {
        $date = Carbon::parse($request->input('date', now()->toDateString()));
        $today = now();
        
        // Get pay period information
        $payPeriod = $this->calculatePayPeriod($date);
        // Ensure occurrences exist for this window
        $this->ensureOccurrences($payPeriod['start_date']->copy(), $payPeriod['end_date']->copy());

        // Load occurrences with parent payment, sorted: date ASC, direction, amount DESC
        $occurrences = PaymentOccurrence::query()
            ->select('payment_occurrences.*')
            ->join('payments', 'payments.id', '=', 'payment_occurrences.payment_id')
            ->where('payments.user_id', Auth::id())
            ->whereBetween('payment_occurrences.date', [$payPeriod['start_date']->toDateString(), $payPeriod['end_date']->toDateString()])
            ->with(['payment'])
            ->orderBy('payment_occurrences.date')
            // Ensure consistent direction ordering: incoming before outgoing
            ->orderByRaw("CASE payments.direction WHEN 'incoming' THEN 0 WHEN 'outgoing' THEN 1 ELSE 2 END")
            ->orderByDesc('payments.amount')
            ->get();

        // Totals from occurrences (signed): incoming positive, outgoing negative
        $incomingTotal = (float) $occurrences->filter(fn($o) => $o->payment->direction === 'incoming')->sum(fn($o) => (float)$o->payment->amount);
        $outgoingTotal = -1 * (float) $occurrences->filter(fn($o) => $o->payment->direction === 'outgoing')->sum(fn($o) => (float)$o->payment->amount);
        $netTotal = $incomingTotal + $outgoingTotal;
        $remainingUnpaid = -1 * (float) $occurrences
            ->filter(fn($o) => $o->payment->direction === 'outgoing' && $o->status !== 'paid')
            ->sum(fn($o) => (float)$o->payment->amount);

        return view('budget.index', [
            'date' => $date,
            'previousMonth' => $date->copy()->subMonth()->toDateString(),
            'nextMonth' => $date->copy()->addMonth()->toDateString(),
            'today' => $today,
            'payPeriod' => $payPeriod,
            'occurrences' => $occurrences,
            'incomingTotal' => $incomingTotal,
            'outgoingTotal' => $outgoingTotal,
            'netTotal' => $netTotal,
            'remainingUnpaid' => $remainingUnpaid,
        ]);
    }

    protected function ensureOccurrences(Carbon $windowStart, Carbon $windowEnd): void
    {
        $userId = Auth::id();
        if (!$userId) return;

        // Base payments that could have an occurrence in the window
        $payments = Payment::where('user_id', $userId)
            ->where(function ($q) use ($windowEnd) {
                $q->where('date', '<=', $windowEnd->toDateString());
            })
            ->get();

        foreach ($payments as $payment) {
            // Determine first and last possible dates for occurrences
            $start = max(strtotime($windowStart->toDateString()), strtotime($payment->date->toDateString()));
            $endLimit = $windowEnd->copy();
            if ($payment->repeatable && $payment->repeat_end_date) {
                $endLimit = min($endLimit, Carbon::parse($payment->repeat_end_date));
            }

            // Non-repeatable: only one on the base date if inside window
            if (!$payment->repeatable || !$payment->frequency) {
                $occDate = $payment->date->toDateString();
                if ($occDate >= $windowStart->toDateString() && $occDate <= $windowEnd->toDateString()) {
                    PaymentOccurrence::firstOrCreate([
                        'payment_id' => $payment->id,
                        'date' => $occDate,
                    ], [
                        'status' => $payment->status,
                    ]);
                }
                continue;
            }

            // Repeatable: iterate dates by frequency
            $cursor = Carbon::createFromTimestamp($start);
            $last = $endLimit->copy();
            while ($cursor->lte($last)) {
                // Only generate on cadence relative to base date
                if ($this->matchesCadence($payment->date, $cursor, $payment->frequency)) {
                    PaymentOccurrence::firstOrCreate([
                        'payment_id' => $payment->id,
                        'date' => $cursor->toDateString(),
                    ], [
                        'status' => 'pending',
                    ]);
                }
                // Step daily within window to test cadence efficiently
                $cursor->addDay();
            }
        }
    }

    protected function matchesCadence(Carbon $base, Carbon $candidate, string $frequency): bool
    {
        switch ($frequency) {
            case 'daily':
                return $candidate->gte($base);
            case 'weekly':
                return $candidate->isSameDay($base) || ($candidate->isMonday() === $base->isMonday() && $candidate->diffInDays($base) % 7 === 0) || ($candidate->dayOfWeek === $base->dayOfWeek);
            case 'monthly':
                return $candidate->day === $base->day;
            case 'yearly':
                return $candidate->format('m-d') === $base->format('m-d');
            default:
                return false;
        }
    }

    /**
     * Update an existing payment
     */
    public function updatePayment(Request $request, Payment $payment)
    {
        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'date' => 'sometimes|required|date',
            'amount' => 'sometimes|required|numeric',
            'name' => 'sometimes|required|string|max:255',
            'direction' => 'sometimes|required|in:incoming,outgoing',
            'repeatable' => 'nullable|boolean',
            'frequency' => 'nullable|in:weekly,monthly,yearly',
            'repeat_end_date' => 'nullable|date|after_or_equal:date',
        ]);

        // Ensure checkbox false when absent
        if (!$request->has('repeatable')) {
            $validated['repeatable'] = false;
        }

        $payment->update($validated);

        return redirect()->route('budget.index')->with('success', 'Payment updated.');
    }

    /**
     * Mark payment as paid
     */
    public function markPaymentPaid(Payment $payment)
    {
        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        $payment->update(['status' => 'paid']);

        return redirect()->back()->with('success', 'Payment marked as paid.');
    }

    /**
     * Mark a specific occurrence as paid
     */
    public function markOccurrencePaid(PaymentOccurrence $occurrence)
    {
        if ($occurrence->payment->user_id !== Auth::id()) {
            abort(403);
        }
        $occurrence->update(['status' => 'paid']);
        return redirect()->back()->with('success', 'Occurrence marked as paid.');
    }

    /**
     * Mark a specific occurrence as failed
     */
    public function markOccurrenceFailed(PaymentOccurrence $occurrence)
    {
        if ($occurrence->payment->user_id !== Auth::id()) {
            abort(403);
        }
        $occurrence->update(['status' => 'failed']);
        return redirect()->back()->with('success', 'Occurrence marked as failed.');
    }

    /**
     * Mark a specific occurrence as unpaid (pending)
     */
    public function markOccurrenceUnpaid(PaymentOccurrence $occurrence)
    {
        if ($occurrence->payment->user_id !== Auth::id()) {
            abort(403);
        }
        $occurrence->update(['status' => 'pending']);
        return redirect()->back()->with('success', 'Occurrence marked as unpaid.');
    }

    /**
     * Delete a payment
     */
    public function destroyPayment(Payment $payment)
    {
        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        $payment->delete();

        return redirect()->route('budget.index')->with('success', 'Payment deleted.');
    }

    /**
     * Show Budget Settings page
     */
    public function settings(Request $request)
    {
        $startDay = 28;
        $mode = 'monthly';
        if (Schema::hasTable('budget_settings')) {
            $userId = Auth::id();
            $row = null;
            if ($userId) {
                $row = DB::table('budget_settings')->where('user_id', $userId)->first();
            }
            if (!$row) {
                $row = DB::table('budget_settings')->where('id', 1)->first();
            }
            if ($row) {
                if (is_numeric($row->pay_period_start_day) && $row->pay_period_start_day >= 1 && $row->pay_period_start_day <= 31) {
                    $startDay = (int) $row->pay_period_start_day;
                }
                if (!empty($row->pay_period_mode)) {
                    $mode = in_array($row->pay_period_mode, ['monthly','weekly']) ? $row->pay_period_mode : 'monthly';
                }
            }
        }

        return view('budget.settings', [
            'startDay' => $startDay,
            'mode' => $mode,
        ]);
    }

    /**
     * Update Budget Settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'pay_period_start_day' => 'required|integer|min:1|max:31',
            'pay_period_mode' => 'required|in:monthly,weekly',
        ]);

        // Ensure table exists
        if (!Schema::hasTable('budget_settings')) {
            return redirect()->route('budget.settings')->withErrors('Settings table not found. Please run migrations.');
        }

        $userId = Auth::id();
        // Upsert per-user row
        $existing = DB::table('budget_settings')->where('user_id', $userId)->first();
        if ($existing) {
            DB::table('budget_settings')->where('user_id', $userId)->update([
                'pay_period_start_day' => $validated['pay_period_start_day'],
                'pay_period_mode' => $validated['pay_period_mode'],
                'updated_at' => now(),
            ]);
        } else {
            DB::table('budget_settings')->insert([
                'user_id' => $userId,
                'pay_period_start_day' => $validated['pay_period_start_day'],
                'pay_period_mode' => $validated['pay_period_mode'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('budget.settings')->with('success', 'Budget settings updated.');
    }

    /**
     * Store a new payment
     */
    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'name' => 'required|string|max:255',
            'direction' => 'required|in:incoming,outgoing',
            'repeatable' => 'nullable|boolean',
            'frequency' => 'nullable|in:weekly,monthly,yearly',
            'repeat_end_date' => 'nullable|date|after_or_equal:date',
        ]);

        Payment::create([
            'user_id' => Auth::id(),
            'date' => $validated['date'],
            'amount' => $validated['amount'],
            'name' => $validated['name'],
            'direction' => $validated['direction'],
            'repeatable' => (bool)($validated['repeatable'] ?? false),
            'frequency' => $validated['frequency'] ?? null,
            'repeat_end_date' => $validated['repeat_end_date'] ?? null,
        ]);

        return redirect()->route('budget.index')->with('success', 'Payment added.');
    }

    /**
     * Store a newly created budget
     */
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'due_date' => 'required|date',
    //         'type' => 'required|in:one_time,recurring,habit',
    //         'frequency' => 'required_if:type,habit',
    //         'recurring_frequency' => 'required_if:type,recurring',
    //         'recurrence_ends_at' => 'nullable|date|after_or_equal:due_date',
    //         'target_count' => 'required_if:type,habit|integer|min:1',
    //         'is_skippable' => 'boolean',
    //     ]);

    //     $budgetData = [
    //         'title' => $validated['title'],
    //         'description' => $validated['description'] ?? null,
    //         'due_date' => $validated['due_date'],
    //         'completed' => false,
    //         'type' => $validated['type'],
    //     ];

    //     // Handle recurring task fields
    //     if ($validated['type'] === 'recurring') {
    //         $todoData['frequency'] = $validated['recurring_frequency'];
    //         if (!empty($validated['recurrence_ends_at'])) {
    //             $todoData['recurrence_ends_at'] = $validated['recurrence_ends_at'];
    //         }
    //     }

    //     // Handle habit fields
    //     if ($validated['type'] === 'habit') {
    //         $todoData['frequency'] = $validated['frequency'];
    //         $todoData['target_count'] = $validated['target_count'];
    //         $todoData['is_skippable'] = $request->has('is_skippable');
    //         $todoData['current_streak'] = 0;
    //         $todoData['longest_streak'] = 0;
    //     }

    //     $todo = Auth::user()->todos()->create($todoData);

    //     return redirect()
    //         ->route('planner.index', ['date' => Carbon::parse($validated['due_date'])->toDateString()])
    //         ->with('success', 'Task created successfully!');
    // }

    /**
     * Toggle payment status
     */
    /**
     * Show the form for editing the specified todo
     */
    // public function edit(Todo $todo)
    // {
    //     $this->authorize('update', $todo);
        
    //     return view('planner._edit_form', [
    //         'todo' => $todo
    //     ]);
    // }

    /**
     * Toggle todo completion status
     */
    // public function toggleComplete(Todo $todo, Request $request)
    // {
    //     $this->authorize('update', $todo);
        
    //     $date = $request->input('date', now()->toDateString());
    //     $change = (int)$request->input('change', 1);
        
    //     if ($todo->is_habit) {
    //         // For habits, handle the completion count change
    //         if ($change > 0) {
    //             // Increment the count (up to target)
    //             $todo->incrementCompletion($date, $change);
    //             $message = 'Habit updated for ' . Carbon::parse($date)->format('M j, Y');
    //         } else if ($change < 0) {
    //             // Decrement the count (but not below 0)
    //             $todo->decrementCompletion($date, abs($change));
    //             $message = 'Habit updated for ' . Carbon::parse($date)->format('M j, Y');
    //         } else {
    //             // No change, just get current status
    //             $message = 'Habit status checked for ' . Carbon::parse($date)->format('M j, Y');
    //         }
            
    //         // Get the updated completion count for the response
    //         $completionCount = $todo->getCompletionCount($date);
    //         $completed = $completionCount >= $todo->target_count;
    //     } else {
    //         // For regular todos, just toggle the completed status
    //         $completed = !$todo->completed;
    //         $todo->update(['completed' => $completed]);
    //         $message = $completed ? 'Task completed!' : 'Task marked as incomplete';
    //     }

    //     if ($request->ajax()) {
    //         return response()->json([
    //             'success' => true,
    //             'message' => $message,
    //             'completed' => $completed,
    //             'completion_count' => $todo->is_habit ? $todo->getCompletionCount($date) : null,
    //             'target_count' => $todo->is_habit ? $todo->target_count : null
    //         ]);
    //     }

    //     return redirect()
    //         ->route('planner.index', ['date' => $date])
    //         ->with('success', $message);
    // }

    /**
     * Update the specified todo
     */
    // public function update(Request $request, Todo $todo)
    // {
    //     $this->authorize('update', $todo);

    //     $validated = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'due_date' => 'required|date',
    //         'completed' => 'boolean',
    //         'type' => 'required|in:one_time,recurring,habit',
    //         'frequency' => 'required_if:type,habit',
    //         'recurring_frequency' => 'required_if:type,recurring',
    //         'recurrence_ends_at' => 'nullable|date|after_or_equal:due_date',
    //         'target_count' => 'required_if:type,habit|integer|min:1',
    //         'is_skippable' => 'boolean',
    //     ]);

    //     $updateData = [
    //         'title' => $validated['title'],
    //         'description' => $validated['description'] ?? null,
    //         'due_date' => $validated['due_date'],
    //         'type' => $validated['type'],
    //     ];

    //     // Handle completed status
    //     if ($request->has('completed')) {
    //         $updateData['completed'] = $validated['completed'];
    //     }

    //     // Handle recurring task fields
    //     if ($validated['type'] === 'recurring') {
    //         $updateData['frequency'] = $validated['recurring_frequency'];
    //         $updateData['recurrence_ends_at'] = $validated['recurrence_ends_at'] ?? null;
            
    //         // Set default values for non-recurring fields
    //         $updateData['target_count'] = 1;
    //         $updateData['current_streak'] = 0;
    //         $updateData['longest_streak'] = 0;
    //         $updateData['is_skippable'] = true;
    //     }
    //     // Handle habit fields
    //     elseif ($validated['type'] === 'habit') {
    //         $updateData['frequency'] = $validated['frequency'];
    //         $updateData['target_count'] = $validated['target_count'];
    //         $updateData['is_skippable'] = $request->has('is_skippable');
            
    //         // Clear recurring-specific fields if type changed from recurring
    //         $updateData['recurrence_ends_at'] = null;
            
    //         // Initialize streaks if this is a new habit
    //         if ($todo->type !== 'habit') {
    //             $updateData['current_streak'] = 0;
    //             $updateData['longest_streak'] = 0;
    //         }
    //     }
    //     // Handle one-time task
    //     else {
    //         // Clear all special fields
    //         $updateData['frequency'] = null;
    //         $updateData['recurrence_ends_at'] = null;
    //         $updateData['target_count'] = null;
    //         $updateData['current_streak'] = null;
    //         $updateData['longest_streak'] = null;
    //         $updateData['is_skippable'] = true;
    //     }

    //     $todo->update($updateData);

    //     return redirect()
    //         ->route('planner.index', ['date' => Carbon::parse($validated['due_date'])->toDateString()])
    //         ->with('success', 'Task updated successfully!');
    // }

    /**
     * Remove the specified todo
     */
    // public function destroy(Todo $todo)
    // {
    //     $this->authorize('delete', $todo);
        
    //     $todo->delete();

    //     return back()->with('success', 'Todo deleted!');
    // }
}
