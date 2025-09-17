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
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('city');
            $table->string('country');

            // Relationships
            $table->foreignId('type_id')->nullable()->constrained('job_post_types')->nullOnDelete();
            $table->foreignId('employer_id')->constrained('employers')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            // Job details
            $table->enum('status', ['draft', 'published', 'closed', 'expired'])->default('published');
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->string('currency', 3)->default('TZS');
            $table->string('experience_level')->nullable();
            $table->string('education_level')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->date('deadline')->nullable();

            $table->boolean('is_hot')->default(false);
            // Analytics
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('applications_count')->default(0);

            // Slug & soft delete
            $table->string('slug')->unique()->nullable();
            $table->softDeletes();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
