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
        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('treatment_id')->nullable()->after('appointment_id');
            $table->decimal('discount_amount', 10, 2)->default(0.00)->after('total_amount');
            $table->decimal('final_amount', 10, 2)->default(0.00)->after('discount_amount');
            $table->decimal('balance_amount', 10, 2)->default(0.00)->after('paid_amount');

            $table->foreign('treatment_id')->references('id')->on('patient_treatments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['treatment_id']);
            $table->dropColumn(['treatment_id', 'discount_amount', 'final_amount', 'balance_amount']);
        });
    }
};
