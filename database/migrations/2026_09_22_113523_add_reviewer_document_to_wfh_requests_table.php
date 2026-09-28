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
        Schema::table('wfh_requests', function (Blueprint $table) {
            $table->string('reviewer_document')->nullable()->after('supporting_document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfh_requests', function (Blueprint $table) {
            $table->dropColumn('reviewer_document');
        });
    }
};
