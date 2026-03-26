<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['nom', 'categorie'];

    public function doctors() {
        return $this->hasMany(Doctor::class);
    }
}
