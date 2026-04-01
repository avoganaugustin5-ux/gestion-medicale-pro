<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // 1. Ajout de la colonne cancel_reason après reason
            if (!Schema::hasColumn('appointments', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable()->after('reason');
            }

            // 2. Ajout de service_id après secretary_id
            if (!Schema::hasColumn('appointments', 'service_id')) {
                $table->foreignId('service_id')->nullable()->after('secretary_id')->constrained()->onDelete('set null');
            }
        });

        // 3. Mise à jour du type ENUM pour inclure 'completed'
        // Note: Sur MySQL, on utilise un statement brut car Blueprint ne supporte pas bien le changement d'ENUM
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn(['cancel_reason', 'service_id']);
        });

        // On remet l'ancien ENUM
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending'");
    }
};