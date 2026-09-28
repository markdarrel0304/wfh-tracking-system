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
        if (! Schema::hasTable('output_attachments')) {
            Schema::create('output_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('accomplishment_report_id')->constrained()->cascadeOnDelete();
                $table->string('file_path');
                $table->string('file_name');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size_bytes')->nullable();
                $table->timestamp('uploaded_at')->useCurrent();
                $table->index(['accomplishment_report_id', 'uploaded_at']);
            });

            return;
        }

        Schema::table('output_attachments', function (Blueprint $table) {
            if (! Schema::hasColumn('output_attachments', 'mime_type')) {
                $table->string('mime_type')->nullable();
            }

            if (! Schema::hasColumn('output_attachments', 'size_bytes')) {
                $table->unsignedBigInteger('size_bytes')->nullable();
            }
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
