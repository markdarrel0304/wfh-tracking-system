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
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->time('lunch_start')->default('12:00:00')->after('time_out');
            $table->time('lunch_end')->default('13:00:00')->after('lunch_start');
            $table->time('overtime_start')->default('17:30:00')->after('lunch_end');
            $table->unsignedSmallInteger('overtime_minimum_minutes')->default(120)->after('overtime_start');
            $table->unsignedSmallInteger('overtime_maximum_minutes')->default(180)->after('overtime_minimum_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'lunch_start',
                'lunch_end',
                'overtime_start',
                'overtime_minimum_minutes',
                'overtime_maximum_minutes',
            ]);
        });
    }
};
