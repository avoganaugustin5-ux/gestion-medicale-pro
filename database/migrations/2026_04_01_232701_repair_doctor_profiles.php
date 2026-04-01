<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Doctor;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // On récupère tous les utilisateurs qui ont le rôle médecin
        $medecins = User::where('role', 'medecin')->get();

        foreach ($medecins as $user) {
            // On crée le profil docteur s'il n'existe pas déjà
            Doctor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name, 
                    'specialty' => 'Généraliste',
                    'clinic_id' => $user->clinic_id ?? 1 
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionnel : supprimer les profils créés par cette migration si besoin
    }
};