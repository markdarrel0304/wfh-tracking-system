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
       Schema::create('wfh_requests', function (Blueprint $table) {
       $table->id();
       $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
       $table->date('date_from');
       $table->date('date_to');
       $table->text('reason');
       $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
       $table->foreignId('approver_id')->nullable()->constrained('employees');
       $table->timestamp('approved_at')->nullable();
       $table->text('remarks')->nullable();
       $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfh_requests');
    }
};
