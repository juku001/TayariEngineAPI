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
        Schema::create('aptitude_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('aptitude_questions')->onDelete('cascade');
            $table->string('title');
            $table->string('key')->nullable();
            $table->string('sub_title')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
        });


        Artisan::call('db:seed', ['--class' => 'DefaultAptitude']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aptitude_options');
    }
};
