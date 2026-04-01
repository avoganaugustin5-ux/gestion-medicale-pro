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
    $medecins = \App\Models\User::where('role', 'medecin')->get();
        foreach ($medecins as $user) {
            \App\Models\Doctor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'specialty' => 'Généraliste',
                    'clinic_id' => $user->clinic_id ?? 1 // ID par défaut
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
