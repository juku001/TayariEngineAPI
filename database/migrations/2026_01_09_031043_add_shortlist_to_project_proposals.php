<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE project_proposals 
            MODIFY status 
            ENUM('pending', 'shortlist', 'accepted', 'denied') 
            NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE project_proposals 
            MODIFY status 
            ENUM('pending', 'accepted', 'denied') 
            NOT NULL
        ");
    }
};
