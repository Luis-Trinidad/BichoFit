<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rutinas semanales: cada línea pertenece a un día (ISO: 1=Lunes…7=Domingo)
        Schema::table('routine_items', function (Blueprint $table) {
            $table->unsignedTinyInteger('day_of_week')->default(1)->after('routine_id');
            $table->index(['routine_id', 'day_of_week', 'position']);
        });

        // El día de la rutina que cubre la sesión (null si libre o rutina sin día)
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->unsignedTinyInteger('routine_day')->nullable()->after('routine_id');
        });
    }

    public function down(): void
    {
        Schema::table('routine_items', function (Blueprint $table) {
            $table->dropIndex(['routine_id', 'day_of_week', 'position']);
            $table->dropColumn('day_of_week');
        });
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->dropColumn('routine_day');
        });
    }
};
