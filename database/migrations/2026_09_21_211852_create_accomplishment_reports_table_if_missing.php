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
        if (! Schema::hasTable('accomplishment_reports')) {
            Schema::create('accomplishment_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->text('summary');
                $table->text('blockers')->nullable();
                $table->text('next_steps')->nullable();
                $table->enum('status', ['submitted', 'reviewed'])->default('submitted');
                $table->timestamp('submitted_at')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('employees');
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->unique(['employee_id', 'date']);
            });

            return;
        }

        Schema::table('accomplishment_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('accomplishment_reports', 'blockers')) {
                $table->text('blockers')->nullable();
            }

            if (! Schema::hasColumn('accomplishment_reports', 'next_steps')) {
                $table->text('next_steps')->nullable();
            }

            if (! Schema::hasColumn('accomplishment_reports', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }

            if (! Schema::hasColumn('accomplishment_reports', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accomplishment_reports');
    }
};
