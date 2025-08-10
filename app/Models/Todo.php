<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'due_date',
        'completed',
        'type',
        'recurrence_pattern',
        'recurrence_ends_at',
        'target_count',
        'frequency',
        'current_streak',
        'longest_streak',
        'last_completed_at',
        'is_skippable',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed' => 'boolean',
        'recurrence_pattern' => 'array',
        'recurrence_ends_at' => 'date',
        'last_completed_at' => 'date',
        'is_skippable' => 'boolean',
    ];

    protected $appends = ['is_habit', 'is_recurring', 'completion_count'];
    
    protected $with = ['completions'];
    
    protected static function booted()
    {
        static::saving(function ($todo) {
            if ($todo->isDirty('completed') && $todo->completed) {
                $todo->last_completed_at = now();
                
                if ($todo->is_habit) {
                    static::updateHabitStreak($todo);
                }
                else if ($todo->is_recurring && (!$todo->recurrence_ends_at || $todo->recurrence_ends_at->isFuture())) {
                    static::createNextRecurringInstance($todo);
                }
            }
        });
    }
    
    public function getIsHabitAttribute()
    {
        return $this->type === 'habit';
    }
    
    public function getIsRecurringAttribute()
    {
        return $this->type === 'recurring';
    }
    
    protected static function updateHabitStreak($todo, $increment = true, $selectedDate = null)
    {
        $frequency = $todo->frequency ?? 'daily';
        $referenceDate = $selectedDate ? Carbon::parse($selectedDate) : now();
        $referenceDate = $referenceDate->setTimezone(config('app.timezone'))->startOfDay();
        
        // Get all unique completion dates, ordered by date (newest first)
        $completions = $todo->completions()
            ->orderBy('completion_date', 'desc')
            ->pluck('completion_date')
            ->map(function ($date) use ($referenceDate) {
                $parsed = Carbon::parse($date)->setTimezone(config('app.timezone'))->startOfDay();
                // Only include dates that are on or before the reference date
                return $parsed->lte($referenceDate) ? $parsed : null;
            })
            ->filter() // Remove null values (future dates relative to reference date)
            ->unique()
            ->values()
            ->toArray();
            
        // Log the completions for debugging
        // \Log::info('Updating streak', [
        //     'todo_id' => $todo->id,
        //     'reference_date' => $referenceDate->toDateString(),
        //     'timezone' => config('app.timezone'),
        //     'completions' => array_map(fn($c) => $c->toDateString(), $completions),
        //     'current_streak' => $todo->current_streak,
        //     'last_completed_at' => $todo->last_completed_at?->toDateString(),
        //     'increment' => $increment
        // ]);
            
        if (empty($completions)) {
            $todo->current_streak = 0;
            $todo->last_completed_at = null;
            $todo->save();
            return;
        }
        
        // For daily habits, we'll use a simpler approach
        if ($frequency === 'daily') {
            // Sort dates in ascending order
            usort($completions, function($a, $b) {
                return $a->timestamp - $b->timestamp;
            });
            
            // Start with the most recent completion (on or before reference date)
            $lastCompletion = end($completions);
            
            // If we have no completions, reset the streak
            if (!$lastCompletion) {
                $todo->current_streak = 0;
                $todo->last_completed_at = null;
                $todo->save();
                return;
            }
            
            // When decrementing, we need to recalculate the entire streak
            // instead of just adjusting by 1
            if (!$increment) {
                $todo->current_streak = 0;
                $todo->last_completed_at = null;
                $todo->save();
                
                // If there are still completions, recalculate the streak from scratch
                if (!empty($completions)) {
                    $newLastCompletion = end($completions);
                    $todo->last_completed_at = $newLastCompletion;
                    $todo->save();
                    return; // The next call will calculate the correct streak
                }
                return;
            }
            
            // Start with a streak of 1 for today
            $currentStreak = 1;
            $currentDate = $lastCompletion->copy()->subDay();
            
            // Go through completions in reverse chronological order
            for ($i = count($completions) - 2; $i >= 0; $i--) {
                if ($completions[$i]->isSameDay($currentDate)) {
                    $currentStreak++;
                    $currentDate->subDay();
                } else {
                    // If we find a gap in the streak, stop checking
                    break;
                }
            }
            
            // Log the calculated streak
            // \Log::info('Calculated streak', [
            //     'todo_id' => $todo->id,
            //     'current_streak' => $currentStreak,
            //     'last_completion' => $lastCompletion->toDateString()
            // ]);
            
            // Update the todo
            $todo->current_streak = $currentStreak;
            $todo->longest_streak = max($currentStreak, $todo->longest_streak ?? 0);
            $todo->last_completed_at = $lastCompletion;
            $todo->save();
            return;
        }
        
        // For weekly and monthly, keep the existing logic but simplified
        $sortedCompletions = collect($completions)
            ->filter(function($date) use ($today) {
                return $date->lte($today);
            })
            ->sort()
            ->values();
            
        if ($sortedCompletions->isEmpty()) {
            $todo->current_streak = 0;
            $todo->last_completed_at = null;
            $todo->save();
            return;
        }
        
        $currentStreak = 1;
        $lastDate = $sortedCompletions->first();
        $lastCompleted = $sortedCompletions->last();
        
        foreach ($sortedCompletions->slice(1) as $currentDate) {
            if ($currentDate->isSameDay($lastDate)) {
                continue;
            }
            
            $isConsecutive = false;
            
            if ($frequency === 'weekly') {
                $expectedNext = $lastDate->copy()->addWeek();
                $isConsecutive = $currentDate->isSameDay($expectedNext);
            } elseif ($frequency === 'monthly') {
                $expectedNext = $lastDate->copy()->addMonth();
                $isConsecutive = $currentDate->isSameDay($expectedNext);
            }
            
            if ($isConsecutive) {
                $currentStreak++;
            } else {
                break;
            }
            
            $lastDate = $currentDate;
        }
        
        // Update the todo
        $todo->current_streak = $currentStreak;
        $todo->longest_streak = max($currentStreak, $todo->longest_streak ?? 0);
        $todo->last_completed_at = $sortedCompletions->last();
        $todo->save();
        
        // Log the update for debugging
        // \Log::info('Updated streak', [
        //     'todo_id' => $todo->id,
        //     'frequency' => $frequency,
        //     'current_streak' => $currentStreak,
        //     'longest_streak' => $todo->longest_streak,
        //     'last_completed' => $todo->last_completed_at,
        //     'completions' => $completions,
        //     'previous_streak' => $todo->getOriginal('current_streak')
        // ]);
    }
    
    protected static function createNextRecurringInstance($todo)
    {
        $nextDueDate = null;
        
        // Use the frequency directly from the model instead of recurrence_pattern
        $frequency = $todo->frequency ?? 'daily';
        
        switch ($frequency) {
            case 'daily':
                $nextDueDate = $todo->due_date->addDay();
                break;
            case 'weekly':
                $nextDueDate = $todo->due_date->addWeek();
                break;
            case 'monthly':
                $nextDueDate = $todo->due_date->addMonth();
                break;
            // Add more frequencies as needed
        }
        
        if ($nextDueDate) {
            $todo->replicate([
                'completed',
                'current_streak',
                'longest_streak',
                'last_completed_at'
            ])->fill([
                'due_date' => $nextDueDate,
                'completed' => false,
            ])->save();
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get all completions for this habit.
     */
    public function completions()
    {
        return $this->hasMany(HabitCompletion::class, 'habit_id');
    }
    
    /**
     * Get the completion record for a specific date.
     */
    public function getCompletionForDate($date = null)
    {
        $date = $date ? Carbon::parse($date) : now();
        return $this->completions()
            ->whereDate('completion_date', $date->toDateString())
            ->first();
    }
    
    /**
     * Get the completion count for a specific date, week, or month.
     */
    public function getCompletionCount($date = null)
    {
        if (!$this->is_habit) {
            return null;
        }
        
        $date = $date ? Carbon::parse($date) : now();
        $dateString = $date->toDateString();
        
        if ($this->frequency === 'daily') {
            // For daily habits, get completion for the specific date
            $completion = $this->completions()
                ->whereDate('completion_date', $dateString)
                ->first();
                
            return $completion ? $completion->count : 0;
        } elseif ($this->frequency === 'weekly') {
            // For weekly habits, get all completions in the same week as the target date
            $startOfWeek = $date->copy()->startOfWeek()->toDateString();
            $endOfWeek = $date->copy()->endOfWeek()->toDateString();
            
            $completions = $this->completions()
                ->whereBetween('completion_date', [$startOfWeek, $endOfWeek])
                ->get();
                
            return $completions->sum('count');
        } else {
            // For monthly habits, get all completions in the same month as the target date
            $startOfMonth = $date->copy()->startOfMonth()->toDateString();
            $endOfMonth = $date->copy()->endOfMonth()->toDateString();
            
            $completions = $this->completions()
                ->whereBetween('completion_date', [$startOfMonth, $endOfMonth])
                ->get();
                
            return $completions->sum('count');
        }
    }
    
    /**
     * Accessor for the completion_count attribute.
     */
    public function getCompletionCountAttribute()
    {
        return $this->completions()->count();
    }
    
    /**
     * Calculate the current streak as of a specific date
     * @param string|Carbon|null $date The reference date (defaults to today)
     * @return int The current streak length
     */
    public function calculateStreakForDate($date = null)
    {
        $date = $date ? Carbon::parse($date) : now();
        $date = $date->setTimezone(config('app.timezone'))->startOfDay();
        
        // Get all unique completion dates up to and including the reference date
        $completions = $this->completions()
            ->whereDate('completion_date', '<=', $date)
            ->orderBy('completion_date', 'desc')
            ->pluck('completion_date')
            ->map(function ($date) {
                return Carbon::parse($date)->startOfDay();
            })
            ->unique()
            ->sort()
            ->values();
            
        if ($completions->isEmpty()) {
            return 0;
        }
        
        // Start with the most recent completion on or before the reference date
        $currentStreak = 0;
        $expectedDate = $date->copy();
        $completions = $completions->sort()->values();
        
        // Check for consecutive days backwards from the reference date
        // We need at least one completion on or before the reference date
        for ($i = $completions->count() - 1; $i >= 0; $i--) {
            $completionDate = $completions[$i];
            
            if ($completionDate->isSameDay($expectedDate)) {
                $currentStreak++;
                $expectedDate->subDay();
            } else if ($completionDate < $expectedDate) {
                // If we find a completion before our expected date, check if it's part of a new streak
                // Only reset if we're not at the beginning of a potential streak
                if ($currentStreak === 0) {
                    // Start a new potential streak from this completion
                    $currentStreak = 1;
                    $expectedDate = $completionDate->copy()->subDay();
                } else {
                    // We're in the middle of a streak and found a gap
                    break;
                }
            }
            // If completion is after expected date, we continue to the next one
        }
        
        return $currentStreak;
    }
    
    /**
     * Increment the completion count for a habit on a specific date.
     */
    public function incrementCompletion($date = null, $count = 1)
    {
        if (!$this->is_habit) {
            return false;
        }
        
        $date = $date ? Carbon::parse($date) : now();
        // Ensure we're using the correct timezone
        $date->setTimezone(config('app.timezone'));
        $date->startOfDay();
        $dateString = $date->toDateString();
        
        // First try to get existing completion
        $completion = $this->completions()
            ->whereDate('completion_date', $dateString)
            ->first();
        
        if ($completion) {
            // Update existing completion
            $newCount = min($completion->count + $count, $this->target_count);
            $completion->update(['count' => $newCount]);
        } else {
            // Create new completion
            $completion = $this->completions()->create([
                'completion_date' => $dateString,
                'count' => min($count, $this->target_count)
            ]);
            
            // Update last completed at when a new completion is created
            $this->last_completed_at = $date;
        }
        
        // Update the streak using the selected date as reference
        static::updateHabitStreak($this, true, $dateString);
        $this->save();
        
        // Refresh the model to get the latest values
        $this->refresh();
        
        return $completion;
    }
    
    /**
     * Decrement the completion count for a habit on a specific date.
     */
    public function decrementCompletion($date = null, $count = 1)
    {
        if (!$this->is_habit) {
            return false;
        }
        
        $date = $date ? Carbon::parse($date) : now();
        $date->setTimezone(config('app.timezone'))->startOfDay();
        $dateString = $date->toDateString();
        
        // Get the completion record
        $completion = $this->completions()
            ->whereDate('completion_date', $dateString)
            ->first();
            
        if (!$completion) {
            return false;
        }
        
        // Calculate new count, ensuring it doesn't go below 0
        $newCount = max(0, $completion->count - $count);
        
        if ($newCount <= 0) {
            // If count would be 0 or less, delete the record
            $completion->delete();
            
            // If we're removing the last completion, update last_completed_at
            if ($this->last_completed_at && $this->last_completed_at->isSameDay($date)) {
                $previousCompletion = $this->completions()
                    ->orderBy('completion_date', 'desc')
                    ->first();
                $this->last_completed_at = $previousCompletion ? $previousCompletion->completion_date : null;
            }
            
            // Update the streak (decrement) using the selected date as reference
            static::updateHabitStreak($this, false, $dateString);
            $this->save();
            $this->refresh();
            
            return true;
        } else {
            // Otherwise, update the count
            $completion->update(['count' => $newCount]);
            
            // Update the streak (decrement) using the selected date as reference
            static::updateHabitStreak($this, false, $dateString);
            $this->save();
            $this->refresh();
            
            return true;
        }
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate($query, $date)
    {
        $date = Carbon::parse($date);
        $dateString = $date->toDateString();
        
        return $query->with(['completions' => function($q) use ($dateString) {
            $q->whereDate('completion_date', $dateString);
        }])->where(function($q) use ($date, $dateString) {
            // For one-time tasks, show only on the due date and not completed
            $q->where(function($q) use ($date) {
                $q->where('type', 'one_time')
                  ->whereDate('due_date', $date);
            })
            // For recurring tasks, show if the recurrence pattern matches the date and not completed on this date
            ->orWhere(function($q) use ($date, $dateString) {
                $q->where('type', 'recurring')
                  ->whereDate('due_date', '<=', $date)
                  ->where(function($q) use ($date) {
                      $q->whereNull('recurrence_ends_at')
                        ->orWhereDate('recurrence_ends_at', '>=', $date);
                  })
                  ->where(function($q) use ($date) {
                      // For daily recurrence
                      $q->where('frequency', 'daily')
                        // Only include if not completed today
                        ->whereDoesntHave('completions', function($q) use ($date) {
                            $q->whereDate('completion_date', $date->toDateString());
                        });
                  })
                  ->orWhere(function($q) use ($date) {
                      // For weekly recurrence (same day of week)
                      $q->where('frequency', 'weekly')
                        ->whereRaw('EXTRACT(DOW FROM due_date) = EXTRACT(DOW FROM ?::timestamp)', [$date->toDateString()])
                        // Only include if not completed this week
                        ->whereDoesntHave('completions', function($q) use ($date) {
                            $startOfWeek = $date->copy()->startOfWeek()->toDateString();
                            $endOfWeek = $date->copy()->endOfWeek()->toDateString();
                            $q->whereBetween('completion_date', [$startOfWeek, $endOfWeek]);
                        });
                  })
                  ->orWhere(function($q) use ($date) {
                      // For monthly recurrence
                      $q->where('frequency', 'monthly')
                        ->where(function($q) use ($date) {
                            $q->whereRaw('EXTRACT(DAY FROM due_date) = ?', [$date->day])
                              ->orWhere(function($q) use ($date) {
                                  $lastDayOfMonth = $date->copy()->endOfMonth()->day;
                                  $q->whereRaw('EXTRACT(DAY FROM due_date) > ?', [$lastDayOfMonth])
                                    ->whereRaw('EXTRACT(DAY FROM ?::date) = ?', [
                                        $date->toDateString(),
                                        $lastDayOfMonth
                                    ]);
                              });
                        })
                        // Only include if not completed this month
                        ->whereDoesntHave('completions', function($q) use ($date) {
                            $startOfMonth = $date->copy()->startOfMonth()->toDateString();
                            $endOfMonth = $date->copy()->endOfMonth()->toDateString();
                            $q->whereBetween('completion_date', [$startOfMonth, $endOfMonth]);
                        });
                  });
            })
            // For habits, show on every day
            ->orWhere('type', 'habit');
        })->addSelect([
            'completion_count' => function($q) use ($dateString) {
                $q->selectRaw('COALESCE(SUM(count), 0)')
                  ->from('habit_completions')
                  ->whereColumn('habit_id', 'todos.id')
                  ->whereDate('completion_date', $dateString);
            }
        ]);
    }

    public function scopeIncomplete($query)
    {
        return $query->where('completed', false);
    }
}
