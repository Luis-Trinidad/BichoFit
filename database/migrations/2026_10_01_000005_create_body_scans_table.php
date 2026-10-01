<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('body_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('scanned_at');
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->decimal('body_fat_pct', 4, 1)->nullable();
            $table->decimal('muscle_mass_kg', 5, 1)->nullable();
            $table->decimal('water_pct', 4, 1)->nullable();
            $table->decimal('bone_mass_kg', 4, 1)->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            $table->unsignedTinyInteger('visceral_fat')->nullable();
            $table->unsignedTinyInteger('metabolic_age')->nullable();
            $table->jsonb('extra')->nullable(); // métricas fuera de esquema
            $table->string('image_path')->nullable(); // captura original (opcional)
            $table->string('source', 10)->default('manual'); // ocr | manual
            $table->timestamps();

            $table->index(['user_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('body_scans');
    }
};
