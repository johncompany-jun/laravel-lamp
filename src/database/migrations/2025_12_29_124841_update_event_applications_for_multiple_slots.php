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
        // Add cart transport fields if they don't exist
        if (!Schema::hasColumn('event_applications', 'can_help_setup')) {
            Schema::table('event_applications', function (Blueprint $table) {
                $table->boolean('can_help_setup')->default(false)->after('availability');
                $table->boolean('can_help_cleanup')->default(false)->after('can_help_setup');
            });
        }

        // Drop the old unique constraint and add new one
        try {
            Schema::table('event_applications', function (Blueprint $table) {
                $table->dropUnique(['event_id', 'user_id']);
            });
        } catch (\Exception $e) {
            // Constraint may not exist, continue
        }

        try {
            Schema::table('event_applications', function (Blueprint $table) {
                $table->unique(['event_id', 'user_id', 'event_application_slot_id'], 'unique_user_event_slot');
            });
        } catch (\Exception $e) {
            // Constraint may already exist, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_applications', function (Blueprint $table) {
            $table->dropUnique('unique_user_event_slot');
            $table->unique(['event_id', 'user_id']);
            $table->dropColumn(['can_help_setup', 'can_help_cleanup']);
        });
    }
};
