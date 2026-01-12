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
        Schema::table('freelancers', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('description');
            $table->string('whatsapp')->nullable()->after('phone_number');
            $table->boolean('phone_is_whatsapp')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('freelancers', function (Blueprint $table) {
            $table->dropColumn('phone_number');
            $table->dropColumn('whatsapp');
            $table->dropColumn('phone_is_whatsapp');
        });
    }
};
