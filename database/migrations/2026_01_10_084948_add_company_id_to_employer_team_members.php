<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employer_team_members', function (Blueprint $table) {

            // Add company_id
            $table->foreignId('company_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            // Make team_id nullable
            $table->foreignId('team_id')
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employer_team_members', function (Blueprint $table) {

            // Remove company_id
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');

            // Make team_id NOT nullable again
            $table->foreignId('team_id')
                ->nullable(false)
                ->change();
        });
    }
};
