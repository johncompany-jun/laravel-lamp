<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE events MODIFY COLUMN status ENUM('draft', 'open', 'closed', 'cancelled') NOT NULL DEFAULT 'draft'");

        // Migrate existing 'completed' values to 'cancelled'
        DB::table('events')->where('status', 'completed')->update(['status' => 'cancelled']);
    }

    public function down(): void
    {
        DB::table('events')->where('status', 'cancelled')->update(['status' => 'completed']);

        DB::statement("ALTER TABLE events MODIFY COLUMN status ENUM('draft', 'open', 'closed', 'completed') NOT NULL DEFAULT 'draft'");
    }
};
