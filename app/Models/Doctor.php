<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    // C'est cette ligne qui manque ! 
    // Elle autorise Laravel à remplir ces colonnes lors d'un "create"
    protected $fillable = [
        'user_id',
        'first_name', 
        'last_name', 
        'specialty', 
        'service_id',
        'clinic_id'
    ];

    /**
     * Relation : Un médecin appartient à une clinique
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}