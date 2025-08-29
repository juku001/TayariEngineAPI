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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('subtitle')->nullable();
            $table->string('description');
            $table->json('objectives')->nullable();
            $table->json('requirements')->nullable();
            $table->enum('language', ['english', 'swahili'])->nullable();
            $table->foreignId('level_id')->nullable()->constrained();
            $table->foreignId('category_id')->nullable()->constrained();
            $table->string('cover_image')->nullable();
            $table->string('cover_video')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_free')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->enum('certificate_type', ['none', 'completion', 'achievement'])->default('completion');
            $table->enum('status', ['draft', 'published', 'suspended', 'archived'])->default('draft');
            $table->json('tags')->nullable();
            $table->decimal('avg_rating',3,1)->default(0);
            $table->foreignId('instructor')->nullable()->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
