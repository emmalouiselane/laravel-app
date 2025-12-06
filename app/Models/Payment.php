<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'amount',
        'name',
        'status',
        'direction',
        'repeatable',
        'frequency',
        'repeat_end_date',
        'category_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'repeatable' => 'boolean',
        'repeat_end_date' => 'date',
    ];

    public function occurrences()
    {
        return $this->hasMany(\App\Models\PaymentOccurrence::class);
    }

    public function category()
    {
        return $this->belongsTo(PaymentCategory::class);
    }
}
