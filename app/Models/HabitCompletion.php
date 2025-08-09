<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabitCompletion extends Model
{
    protected $fillable = [
        'habit_id',
        'completion_date',
        'count',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'count' => 'integer',
    ];

    /**
     * Get the habit that this completion belongs to.
     */
    public function habit(): BelongsTo
    {
        return $this->belongsTo(Todo::class, 'habit_id');
    }
}
