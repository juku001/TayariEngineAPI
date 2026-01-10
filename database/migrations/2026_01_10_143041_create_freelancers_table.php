<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('freelancers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_available')->default(false);
            $table->string('title'); // Job title
            $table->text('description'); // Freelancer description
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('address')->nullable();

            $table->decimal('start_price', 10, 2)->nullable();
            $table->decimal('end_price', 10, 2)->nullable();

            $table->enum('rate', ['hr','day','project','month'])->default('hr');

            $table->enum('currency', ['TZS','USD','EUR'])->default('USD');

            $table->string('responds_in')->nullable();

            $table->decimal('rating', 3, 2)->nullable(); // 0.00 - 5.00
            $table->integer('reviews_count')->default(0);
            $table->integer('projects_completed')->default(0);
            $table->decimal('success_rate', 5, 2)->nullable(); // 0 - 100%

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancers');
    }
};
