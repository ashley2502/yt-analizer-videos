<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('artists')->nullable();
            $table->unsignedSmallInteger('bpm')->nullable();
            $table->string('musical_key')->nullable();
            $table->string('mood')->nullable();
            $table->string('status')->default('idea');
            $table->text('notes')->nullable();
            $table->json('brief')->nullable();
            $table->json('publish_pack')->nullable();
            $table->json('shorts_plan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beats');
    }
};
