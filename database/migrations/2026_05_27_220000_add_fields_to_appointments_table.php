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
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'type')) {
                $table->enum('type', ['checkup', 'treatment', 'followup', 'emergency'])->default('checkup')->after('status');
            }
            if (!Schema::hasColumn('appointments', 'notes')) {
                $table->text('notes')->nullable()->after('type');
            }
            if (!Schema::hasColumn('appointments', 'cancelled_reason')) {
                $table->string('cancelled_reason')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('appointments', 'created_by')) {
                $table->uuid('created_by')->nullable()->after('cancelled_reason');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['type', 'notes', 'cancelled_reason', 'created_by']);
        });
    }
};
