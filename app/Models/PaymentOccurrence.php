<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentOccurrence extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'date',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function payment()
    {
        return $this->belongsTo(\App\Models\Payment::class);
    }
}
