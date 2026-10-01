<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            // Datos del dataset público (importación gymvisual)
            $table->string('source_id', 16)->nullable()->unique();
            $table->text('description_es')->nullable();
            $table->jsonb('instructions_es')->nullable(); // pasos en orden
            $table->string('equipment')->nullable();
            $table->string('target')->nullable(); // músculo objetivo (en)
            $table->jsonb('secondary_muscles')->nullable();
            $table->string('image_path')->nullable(); // relativo a storage/app/public
            $table->string('gif_path')->nullable();
            $table->string('attribution')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropColumn([
                'source_id', 'description_es', 'instructions_es', 'equipment',
                'target', 'secondary_muscles', 'image_path', 'gif_path', 'attribution',
            ]);
        });
    }
};
