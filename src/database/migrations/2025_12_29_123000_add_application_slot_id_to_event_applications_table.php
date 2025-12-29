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
        Schema::table('event_applications', function (Blueprint $table) {
            $table->foreignId('event_application_slot_id')->nullable()->after('event_id')->constrained('event_application_slots')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_applications', function (Blueprint $table) {
            $table->dropForeign(['event_application_slot_id']);
            $table->dropColumn('event_application_slot_id');
        });
    }
};
