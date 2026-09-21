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
        Schema::table('wfh_requests', function (Blueprint $table) {
            $table->string('request_type')->default('Work From Home')->after('employee_id');
            $table->time('start_time')->after('date_from');
            $table->time('end_time')->after('start_time');
            $table->string('supporting_document')->nullable()->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfh_requests', function (Blueprint $table) {
            $table->dropColumn('request_type');
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
            $table->dropColumn('supporting_document');
        });
    }
};
