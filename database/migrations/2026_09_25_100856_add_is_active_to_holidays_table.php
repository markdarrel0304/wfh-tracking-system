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
        if (! Schema::hasColumn('holidays', 'is_active')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('is_recurring');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('holidays', 'is_active')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
