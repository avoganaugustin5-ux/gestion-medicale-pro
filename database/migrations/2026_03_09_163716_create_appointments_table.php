<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            // La Clinique (le dépôt)
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            // Le Médecin concerné
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            // Le Patient qui demande le RDV
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            // La Secrétaire qui valide (peut être vide au début)
            $table->foreignId('secretary_id')->nullable()->constrained()->onDelete('set null');
            
            $table->dateTime('appointment_date');
            $table->text('reason')->nullable();
            
            // Statuts : pending (en attente), confirmed (validé), cancelled (annulé)
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};