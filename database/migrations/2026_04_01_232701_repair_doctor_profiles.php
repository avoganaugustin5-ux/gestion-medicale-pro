<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    // On récupère tous les utilisateurs qui ont le rôle médecin
    $medecins = \App\Models\User::where('role', 'medecin')->get();

    foreach ($medecins as $user) {
        // On crée le profil docteur s'il n'existe pas déjà
        \App\Models\Doctor::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name, // C'est ce champ qui manquait dans tes logs !
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
        //
    }
};
