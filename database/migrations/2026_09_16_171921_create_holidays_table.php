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
        if (! Schema::hasColumn('holidays', 'is_recurring')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->boolean('is_recurring')->default(false)->after('date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('holidays', 'is_recurring')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->dropColumn('is_recurring');
            });
        }
    }
};
