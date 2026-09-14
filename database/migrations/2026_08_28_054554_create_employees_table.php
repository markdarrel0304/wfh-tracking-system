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
       Schema::create('employees', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('employee_number')->unique();
    $table->string('first_name');
    $table->string('last_name');
    $table->foreignId('department_id')->constrained();
    $table->string('position');
    $table->foreignId('work_schedule_id')->constrained();
    $table->date('date_hired');
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
