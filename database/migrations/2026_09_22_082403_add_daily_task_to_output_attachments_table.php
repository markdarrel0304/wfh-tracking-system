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
        Schema::table('output_attachments', function (Blueprint $table) {
            $table->foreignId('daily_task_id')
                ->nullable()
                ->after('accomplishment_report_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['daily_task_id', 'uploaded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('output_attachments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('daily_task_id');
        });
    }
};
