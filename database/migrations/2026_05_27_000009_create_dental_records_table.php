<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_records', function (Blueprint $table) {
$table->uuid('id')->primary();
            $table->uuid('patient_id')->index();
            $table->string('tooth_number', 2)->index(); // FDI notation 11-48
            $table->string('condition');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_records');
    }
};
