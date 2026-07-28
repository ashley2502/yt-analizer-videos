<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('beat_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('category')->default('produce');
            $table->unsignedSmallInteger('minutes')->default(25);
            $table->boolean('is_done')->default(false);
            $table->date('due_date')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_tasks');
    }
};
