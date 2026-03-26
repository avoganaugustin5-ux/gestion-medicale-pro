<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'user_id'];

    /**
     * L'administrateur (User) qui possède/a créé la clinique
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Une clinique possède plusieurs médecins
     */
    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    /**
     * Une clinique possède plusieurs patients
     */
    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * Une clinique possède plusieurs rendez-vous
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}