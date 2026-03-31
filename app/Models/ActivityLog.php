<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'user_name', 'action', 'target', 'clinic_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}