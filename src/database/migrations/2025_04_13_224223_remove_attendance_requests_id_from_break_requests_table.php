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
            $table->dropForeign(['attendance_requests_id']); // 外部キー制約があれば削除
            $table->dropColumn('attendance_requests_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('break_requests', function (Blueprint $table) {
            $table->foreignId('attendance_requests_id')->constrained()->onDelete('cascade');
        });
    }
};
