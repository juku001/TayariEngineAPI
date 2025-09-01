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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            
            $table->integer('duration_min');
            $table->integer('duration_max')->nullable();
            $table->enum('duration_unit',['hours','days','weeks','months','years'])->default('months');

            
            $table->foreignId('employer_id')->constrained('employers')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            
            $table->enum('status', ['active', 'in_review', 'completed'])->default('in_review');
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->string('currency', 3)->default('TZS');
            $table->date('deadline')->nullable();

            
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('proposal_count')->default(0);

            
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
        Schema::dropIfExists('projects');
    }
};
