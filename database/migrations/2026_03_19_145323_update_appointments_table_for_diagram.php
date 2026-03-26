<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        // Ajoute 'date' si elle n'existe pas
        if (!Schema::hasColumn('appointments', 'date')) {
            $table->date('date')->nullable()->after('id');
        }

        // Ajoute 'heure' si elle n'existe pas
        if (!Schema::hasColumn('appointments', 'heure')) {
            $table->time('heure')->nullable()->after('date');
        }

        // Ajoute 'service_id' si elle n'existe pas
        if (!Schema::hasColumn('appointments', 'service_id')) {
            $table->foreignId('service_id')->nullable()->constrained();
        }

        // Ajoute 'secretary_id' si elle n'existe pas (l'erreur venait d'ici)
        if (!Schema::hasColumn('appointments', 'secretary_id')) {
            $table->foreignId('secretary_id')->nullable()->constrained();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
