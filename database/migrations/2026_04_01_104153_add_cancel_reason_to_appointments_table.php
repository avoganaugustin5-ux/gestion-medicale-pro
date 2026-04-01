<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // On vérifie si la colonne n'existe PAS avant de l'ajouter
            if (!Schema::hasColumn('appointments', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable()->after('reason');
            }

            // On vérifie si service_id n'existe PAS
            if (!Schema::hasColumn('appointments', 'service_id')) {
                $table->foreignId('service_id')->nullable()->after('secretary_id')->constrained()->onDelete('set null');
            }
        });

        // Mise à jour de l'ENUM (SQL brut pour éviter les conflits)
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // On vérifie qu'elles existent avant de tenter de les supprimer (sécurité inverse)
            if (Schema::hasColumn('appointments', 'service_id')) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            }
            if (Schema::hasColumn('appointments', 'cancel_reason')) {
                $table->dropColumn('cancel_reason');
            }
        });
    }
};