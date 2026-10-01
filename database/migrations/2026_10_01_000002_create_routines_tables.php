<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('routine_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('position');
            $table->string('target')->nullable(); // objetivo libre, ej. "3x8-12"
            $table->timestamps();

            // Sin unique: el swap de posiciones lo viola transitoriamente;
            // el orden 1..n lo mantiene la lógica de recompactación.
            $table->index(['routine_id', 'position']);
            $table->index(['routine_id', 'exercise_id']);
        });

        // Conecta la sesión con la rutina que la originó (Fase 2)
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->foreign('routine_id')->references('id')->on('routines')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->dropForeign(['routine_id']);
        });
        Schema::dropIfExists('routine_items');
        Schema::dropIfExists('routines');
    }
};
