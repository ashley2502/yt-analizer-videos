<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('week_start');
            $table->unsignedTinyInteger('beat_goal')->default(2);
            $table->unsignedTinyInteger('shorts_goal')->default(4);
            $table->unsignedSmallInteger('session_streak')->default(0);
            $table->date('last_session_date')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_weeks');
    }
};
