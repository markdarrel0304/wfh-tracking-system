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
        Schema::table('departments', function (Blueprint $table): void {
            $table->string('code', 20)->nullable()->unique()->after('name');
            $table->string('location', 100)->default('Head Office')->after('code');
            $table->string('department_head', 150)->nullable()->after('location');
            $table->boolean('is_active')->default(true)->after('department_head');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table): void {
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'location', 'department_head', 'is_active']);
        });
    }
};
