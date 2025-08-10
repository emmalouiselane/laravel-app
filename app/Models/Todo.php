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
    
    protected static function updateHabitStreak($todo)
    {
        $yesterday = now()->subDay();
        
        // If last completed was yesterday or earlier, increment streak
        if (!$todo->last_completed_at || $todo->last_completed_at->isSameDay($yesterday)) {
            $todo->current_streak++;
            
            // Update longest streak if needed
            if ($todo->current_streak > $todo->longest_streak) {
                $todo->longest_streak = $todo->current_streak;
            }
        } 
        // If last completed was before yesterday, reset streak
        elseif ($todo->last_completed_at && $todo->last_completed_at->lt($yesterday)) {
            $todo->current_streak = 1;
        }
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
     * Get the completion count for a specific date.
     */
    public function getCompletionCount($date = null)
    {
        if (!$this->is_habit) {
            return null;
        }
        
        $date = $date ? Carbon::parse($date) : now();
        $dateString = $date->toDateString();
        
        $completion = $this->completions()
            ->whereDate('completion_date', $dateString)
            ->first();
        
        return $completion ? $completion->count : 0;
    }
    
    /**
     * Accessor for the completion_count attribute.
     */
    public function getCompletionCountAttribute()
    {
        return $this->getCompletionCount();
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
        $dateString = $date->toDateString();
        
        // First try to get existing completion
        $completion = $this->completions()
            ->whereDate('completion_date', $dateString)
            ->first();
        
        if ($completion) {
            // Update existing completion
            $newCount = min($completion->count + $count, $this->target_count);
            $completion->update(['count' => $newCount]);
            return $completion;
        } else {
            // Create new completion
            return $this->completions()->create([
                'completion_date' => $dateString,
                'count' => min($count, $this->target_count)
            ]);
        }
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
            return $completion->delete();
        } else {
            // Otherwise, update the count
            return $completion->update(['count' => $newCount]);
        }
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate($query, $date)
    {
        $date = Carbon::parse($date);
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        $dateString = $date->toDateString();
        
        return $query->with(['completions' => function($q) use ($dateString) {
            $q->whereDate('completion_date', $dateString);
        }])->where(function($q) use ($startOfDay, $endOfDay, $date) {
            // For one-time tasks, show only on the due date
            $q->where('type', 'one_time')
              ->whereDate('due_date', $date);
        })->orWhere(function($q) use ($startOfDay, $endOfDay, $date) {
            // For recurring tasks, show if the recurrence pattern matches the date
            $q->where('type', 'recurring')
              ->whereDate('due_date', '<=', $date)
              ->where(function($q) use ($date) {
                  $q->whereNull('recurrence_ends_at')
                    ->orWhereDate('recurrence_ends_at', '>=', $date);
              })
              ->where(function($q) use ($date) {
                  $q->where(function($q) use ($date) {
                      // For daily recurrence
                      $q->where('frequency', 'daily');
                  })->orWhere(function($q) use ($date) {
                      // For weekly recurrence (same day of week)
                      // Map Carbon's dayOfWeek (0-6, Sunday=0) to PostgreSQL DOW (0-6, Sunday=0)
                      // But we need to get the day of week from the original due_date, not the current date
                      $q->where('frequency', 'weekly')
                        ->whereRaw('EXTRACT(DOW FROM due_date) = EXTRACT(DOW FROM ?::timestamp)', [$date->toDateString()]);
                  })->orWhere(function($q) use ($date) {
                      // For monthly recurrence (same day of month or last day if day doesn't exist in month)
                      $q->where('frequency', 'monthly')
                        ->where(function($q) use ($date) {
                            // Either the day matches exactly
                            $q->whereRaw('EXTRACT(DAY FROM due_date) = ?', [$date->day])
                              // Or it's the last day of the month and the due date's day is >= the current day
                              ->orWhere(function($q) use ($date) {
                                  $lastDayOfMonth = $date->copy()->endOfMonth()->day;
                                  $q->whereRaw('EXTRACT(DAY FROM due_date) > ?', [$lastDayOfMonth])
                                    ->whereRaw('EXTRACT(DAY FROM ?::date) = ?', [
                                        $date->toDateString(),
                                        $lastDayOfMonth
                                    ]);
                              });
                        });
                  });
              });
        })->orWhere(function($q) {
            // For habits, show on every day
            $q->where('type', 'habit');
        })->addSelect([
            // Add a subquery to get the completion count for the specific date
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
