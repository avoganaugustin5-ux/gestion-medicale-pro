<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_secretary', function (Blueprint $table) {
            $table->id();
            // Le médecin (doit être un utilisateur avec le rôle medecin)
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            // La secrétaire (doit être un utilisateur avec le rôle secretaire)
            $table->foreignId('secretary_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_secretary');
    }
};