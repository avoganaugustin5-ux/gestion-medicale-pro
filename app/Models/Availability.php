<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    protected $fillable = [
        'user_id', 
        'clinic_id', 
        'date', 
        'start_time', 
        'end_time', 
        'is_booked', 
        'status', 
        'note'
    ];

    protected $casts = [
        'is_booked' => 'boolean',
        'date' => 'date',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}