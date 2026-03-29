<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Clinic; // Correction : Clinic au lieu de Clinique
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Création du SUPER ADMINISTRATEUR (Augustin)
        // Ce compte n'est pas lié à une clinique car il les gère toutes
        $admin = User::updateOrCreate(
            ['email' => 'avoganaugustin5@gmail.com'],
            [
                'name' => 'Augustin SuperAdmin',
                'password' => Hash::make('Augustin.@8'),
                'role' => 'admin',
                'clinic_id' => null, 
                'sexe' => 'M', // Ajout pour respecter ton modèle
                'nationalite' => 'Burkinabé',
            ]
        );

        // 2. Création d'une Clinique de test
        // Note : J'utilise 'name' et 'user_id' car ils sont dans ton $fillable
        $cliniqueDemo = Clinic::updateOrCreate(
            ['name' => 'CHU Yalgado Ouédraogo'],
            [
                'description' => 'Centre Hospitalier Universitaire de Ouagadougou',
                'user_id' => $admin->id, // L'admin est le créateur/propriétaire
            ]
        );

        // 3. Création d'un compte MÉDECIN lié à cette clinique
        User::updateOrCreate(
            ['email' => 'medecin@test.bf'],
            [
                'name' => 'Docteur KAFANDO',
                'password' => Hash::make('password123'),
                'role' => 'medecin',
                'clinic_id' => $cliniqueDemo->id,
                'sexe' => 'M',
            ]
        );
    }
}