<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('learner_aptitude_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->json('interests')->nullable();
            $table->string('skill_level')->nullable();
            $table->json('career_goals')->nullable();
            $table->json('recommended_courses');
            
            $table->decimal('logical_score')->nullable();
            $table->decimal('quantitative_score')->nullable();
            $table->decimal('verbal_score')->nullable();
            $table->decimal('total_score')->nullable();
            
            $table->json('answers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learner_aptitude_results');
    }
};
