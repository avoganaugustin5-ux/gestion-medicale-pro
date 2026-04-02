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
        Schema::table('availabilities', function (Blueprint $table) {
            // On ajoute 'status' et on en profite pour vérifier 'note' et 'is_booked' 
            // au cas où ils manqueraient aussi en production
            if (!Schema::hasColumn('availabilities', 'status')) {
                $table->string('status')->default('available')->after('end_time');
            }
            
            if (!Schema::hasColumn('availabilities', 'is_booked')) {
                $table->boolean('is_booked')->default(false)->after('status');
            }

            if (!Schema::hasColumn('availabilities', 'note')) {
                $table->text('note')->nullable()->after('is_booked');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_booked', 'note']);
        });
    }
};