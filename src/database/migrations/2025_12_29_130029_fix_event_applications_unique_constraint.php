<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, check if the old unique constraint exists
        $indexes = DB::select("SHOW INDEX FROM event_applications WHERE Key_name = 'event_applications_event_id_user_id_unique'");

        if (!empty($indexes)) {
            // Temporarily drop foreign key constraints
            Schema::table('event_applications', function (Blueprint $table) {
                $table->dropForeign(['event_id']);
                $table->dropForeign(['user_id']);
            });

            // Drop the old unique index
            DB::statement('ALTER TABLE event_applications DROP INDEX event_applications_event_id_user_id_unique');

            // Re-add foreign keys
            Schema::table('event_applications', function (Blueprint $table) {
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Add the new unique constraint if it doesn't exist
        $indexes = DB::select("SHOW INDEX FROM event_applications WHERE Key_name = 'unique_user_event_slot'");

        if (empty($indexes)) {
            Schema::table('event_applications', function (Blueprint $table) {
                $table->unique(['event_id', 'user_id', 'event_application_slot_id'], 'unique_user_event_slot');
            });
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
        });
    }
};
