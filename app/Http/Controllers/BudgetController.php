<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\Payment;
use App\Models\PaymentOccurrence;
use App\Models\PaymentCategory;
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
                    $mode = in_array($row->pay_period_mode, ['monthly', 'weekly']) ? $row->pay_period_mode : 'monthly';
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
            ->with(['payment', 'payment.category'])
            ->orderBy('payment_occurrences.date')
            // Ensure consistent direction ordering: incoming before outgoing
            ->orderByRaw("CASE payments.direction WHEN 'incoming' THEN 0 WHEN 'outgoing' THEN 1 ELSE 2 END")
            ->orderByDesc('payments.amount')
            ->get();

        // Get user's categories
        $categories = PaymentCategory::where('user_id', Auth::id())->orderBy('name')->get();

        // Totals from occurrences (signed): incoming positive, outgoing negative
        $incomingTotal = (float) $occurrences->filter(fn($o) => $o->payment->direction === 'incoming')->sum(fn($o) => (float) $o->payment->amount);
        $outgoingTotal = -1 * (float) $occurrences->filter(fn($o) => $o->payment->direction === 'outgoing')->sum(fn($o) => (float) $o->payment->amount);
        $netTotal = $incomingTotal + $outgoingTotal;
        $remainingUnpaid = -1 * (float) $occurrences
            ->filter(fn($o) => $o->payment->direction === 'outgoing' && $o->status !== 'paid')
            ->sum(fn($o) => (float) $o->payment->amount);

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
            'categories' => $categories,
        ]);
    }

    protected function ensureOccurrences(Carbon $windowStart, Carbon $windowEnd): void
    {
        $userId = Auth::id();
        if (!$userId)
            return;

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
            'category_id' => 'nullable|exists:payment_categories,id',
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
                    $mode = in_array($row->pay_period_mode, ['monthly', 'weekly']) ? $row->pay_period_mode : 'monthly';
                }
            }
        }

        $categories = PaymentCategory::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        $icons = [
            'archive-box',
            'arrow-path',
            'at-symbol',
            'banknotes',
            'battery-50',
            'beaker',
            'bell',
            'book-open',
            'bookmark',
            'briefcase',
            'bug-ant',
            'building-library',
            'building-office',
            'building-storefront',
            'cake',
            'calculator',
            'calendar',
            'calendar-days',
            'camera',
            'chat-bubble-left',
            'check-circle',
            'clock',
            'cloud',
            'cog',
            'computer-desktop',
            'credit-card',
            'currency-dollar',
            'device-phone-mobile',
            'document-text',
            'envelope',
            'exclamation-triangle',
            'eye',
            'film',
            'finger-print',
            'fire',
            'flag',
            'folder',
            'gift',
            'globe-alt',
            'hand-thumb-up',
            'hashtag',
            'heart',
            'home',
            'identification',
            'inbox',
            'information-circle',
            'key',
            'lifebuoy',
            'light-bulb',
            'link',
            'lock-closed',
            'map-pin',
            'megaphone',
            'microphone',
            'minus-circle',
            'moon',
            'musical-note',
            'paper-airplane',
            'phone',
            'photo',
            'play',
            'plus-circle',
            'printer',
            'puzzle-piece',
            'question-mark-circle',
            'radio',
            'receipt-percent',
            'rocket-launch',
            'rss',
            'scale',
            'server',
            'share',
            'shield-check',
            'shopping-cart',
            'sparkles',
            'speaker-wave',
            'star',
            'stop',
            'sun',
            'tag',
            'ticket',
            'trash',
            'trophy',
            'truck',
            'user',
            'user-group',
            'video-camera',
            'wifi',
            'wrench-screwdriver',
            'x-circle'
        ];

        return view('budget.settings', [
            'startDay' => $startDay,
            'mode' => $mode,
            'categories' => $categories,
            'icons' => $icons,
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
            'category_id' => 'nullable|exists:payment_categories,id',
        ]);

        Payment::create([
            'user_id' => Auth::id(),
            'date' => $validated['date'],
            'amount' => $validated['amount'],
            'name' => $validated['name'],
            'direction' => $validated['direction'],
            'repeatable' => (bool) ($validated['repeatable'] ?? false),
            'frequency' => $validated['frequency'] ?? null,
            'repeat_end_date' => $validated['repeat_end_date'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
        ]);

        return redirect()->route('budget.index')->with('success', 'Payment added.');
    }

    /**
     * Store a new payment category
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
        ]);

        // Check if category name already exists for this user
        $existing = PaymentCategory::where('user_id', Auth::id())
            ->where('name', $validated['name'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Category with this name already exists.');
        }

        PaymentCategory::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'color' => $validated['color'],
            'icon' => $validated['icon'] ?? null,
        ]);

        return back()->with('success', 'Category added.')->with('active_tab', 'categories');
    }

    /**
     * Update a payment category
     */
    public function updateCategory(Request $request, PaymentCategory $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
        ]);

        // Check if category name already exists for this user (excluding current category)
        $existing = PaymentCategory::where('user_id', Auth::id())
            ->where('name', $validated['name'])
            ->where('id', '!=', $category->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Category with this name already exists.');
        }

        $category->update($validated);

        return back()->with('success', 'Category updated.')->with('active_tab', 'categories');
    }

    /**
     * Delete a payment category
     */
    public function destroyCategory(PaymentCategory $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if category is being used by any payments
        if ($category->payments()->count() > 0) {
            return back()->with('error', 'Cannot delete category that is in use by payments.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.')->with('active_tab', 'categories');
    }
}
