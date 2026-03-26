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
    Schema::create('clinics', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Nom de la clinique
        $table->text('description')->nullable();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Le créateur (fondateur)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // On ajoute l'ID de l'utilisateur connecté avant de créer
        $request->user()->clinics()->create($validated);

        return redirect()->route('dashboard')->with('message', 'Clinique créée avec succès !');
    }
};
