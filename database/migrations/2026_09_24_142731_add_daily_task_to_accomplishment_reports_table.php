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
        $hasDailyReportUniqueIndex = collect(Schema::getIndexes('accomplishment_reports'))
            ->contains(fn (array $index): bool => $index['name'] === 'accomplishment_reports_employee_id_date_unique');

        if ($hasDailyReportUniqueIndex) {
            Schema::table('accomplishment_reports', function (Blueprint $table) {
                $table->index('employee_id');
            });

            Schema::table('accomplishment_reports', function (Blueprint $table) {
                $table->dropUnique('accomplishment_reports_employee_id_date_unique');
            });
        }

        Schema::table('accomplishment_reports', function (Blueprint $table) {
            $table->foreignId('daily_task_id')
                ->nullable()
                ->after('employee_id')
                ->constrained()
                ->nullOnDelete();
            $table->unique('daily_task_id');
            $table->index(['employee_id', 'date', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accomplishment_reports', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'date', 'submitted_at']);
            $table->dropUnique('accomplishment_reports_daily_task_id_unique');
            $table->dropConstrainedForeignId('daily_task_id');
            $table->unique(['employee_id', 'date']);
            $table->dropIndex('accomplishment_reports_employee_id_index');
        });
    }
};
