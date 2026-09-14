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
        Schema::create('output_attachments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('accomplishment_report_id')->constrained()->cascadeOnDelete();
        $table->string('file_path');
        $table->string('file_name');
        $table->timestamp('uploaded_at')->useCurrent();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('output_attachments');
    }
};
