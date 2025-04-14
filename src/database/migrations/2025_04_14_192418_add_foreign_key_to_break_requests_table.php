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
        Schema::table('break_requests', function (Blueprint $table) {
            $table->foreign('attendance_request_id')
                ->references('id')
                ->on('attendance_requests')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('break_requests', function (Blueprint $table) {
            $table->dropForeign(['attendance_request_id']);
        });
    }
};
