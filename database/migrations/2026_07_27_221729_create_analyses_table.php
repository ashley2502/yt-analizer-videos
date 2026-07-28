<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('beat_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('engine')->default('heuristic');
            $table->string('thumbnail_path')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->json('input')->nullable();
            $table->json('result');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
