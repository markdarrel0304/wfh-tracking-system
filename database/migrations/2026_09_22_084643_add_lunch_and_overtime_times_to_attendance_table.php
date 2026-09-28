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
        Schema::table('attendance', function (Blueprint $table) {
            $table->time('lunch_out')->nullable()->after('time_in');
            $table->time('lunch_in')->nullable()->after('lunch_out');
            $table->time('overtime_in')->nullable()->after('time_out');
            $table->time('overtime_out')->nullable()->after('overtime_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropColumn(['lunch_out', 'lunch_in', 'overtime_in', 'overtime_out']);
        });
    }
};
