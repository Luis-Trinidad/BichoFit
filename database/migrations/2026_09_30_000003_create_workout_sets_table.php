<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('workout_sessions')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('reps');
            $table->decimal('weight_kg', 6, 2);
            $table->timestamps();

            $table->index(['session_id', 'exercise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_sets');
    }
};
