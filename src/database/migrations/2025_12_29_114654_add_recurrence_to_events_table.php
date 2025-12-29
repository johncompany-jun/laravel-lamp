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
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('status');
            $table->string('recurrence_type')->nullable()->after('is_recurring')->comment('weekly, etc.');
            $table->date('recurrence_end_date')->nullable()->after('recurrence_type');
            $table->foreignId('parent_event_id')->nullable()->after('recurrence_end_date')->constrained('events')->onDelete('set null')->comment('For recurring events, points to the original event');
            $table->boolean('is_template')->default(false)->after('parent_event_id')->comment('Can be used as a template for future events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['parent_event_id']);
            $table->dropColumn(['is_recurring', 'recurrence_type', 'recurrence_end_date', 'parent_event_id', 'is_template']);
        });
    }
};
