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
        Schema::table('users', function (Blueprint $table) {
            $table->char('sexe', 1)->nullable(); // 'M' ou 'F'
            $table->string('nationalite')->nullable();
            $table->date('dateNaissance')->nullable();
            $table->string('adresse')->nullable();
            $table->string('numeroTelephone')->nullable(); // String est préférable pour les formats intx
            $table->string('imageProfil')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
