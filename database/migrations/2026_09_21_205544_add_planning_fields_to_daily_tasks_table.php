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
        if (! Schema::hasTable('daily_tasks')) {
            Schema::create('daily_tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->string('title')->nullable();
                $table->text('task_description');
                $table->string('priority')->default('normal');
                $table->time('due_time')->nullable();
                $table->enum('status', ['pending', 'in-progress', 'done'])->default('pending');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->index(['employee_id', 'date']);
            });

            return;
        }

        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->string('title')->nullable()->after('date');
            $table->string('priority')->default('normal')->after('task_description');
            $table->time('due_time')->nullable()->after('priority');
            $table->timestamp('completed_at')->nullable()->after('status');
            $table->index(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('daily_tasks')) {
            return;
        }

        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'date']);
            $table->dropColumn(['title', 'priority', 'due_time', 'completed_at']);
        });
    }
};
