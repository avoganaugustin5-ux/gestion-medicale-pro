<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $fillable = ['user_id', 'clinic_id', 'date', 'start_time', 'end_time', 'is_booked'];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}