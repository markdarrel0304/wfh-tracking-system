<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('departments')->whereNotNull('department_head')->update(['department_head' => null]);
        DB::table('departments')->whereIn('code', ['PMMS-OMS', 'CMD-SEZ', 'AGSD-SEZ'])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Removed department-head data and unused department units cannot be restored reliably.
    }
};
