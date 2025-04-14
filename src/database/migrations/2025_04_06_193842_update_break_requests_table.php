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
            $table->dropForeign(['attendance_id']);
            $table->dropColumn('attendance_id');

            // attendance_requests_idカラムを追加
            $table->unsignedBigInteger('attendance_requests_id')->nullable();

            // attendance_requests_idに外部キー制約を追加
            $table->foreign('attendance_requests_id')->references('id')->on('attendance_requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('break_requests', function (Blueprint $table) {
            $table->dropForeign(['attendance_requests_id']);
            $table->dropColumn('attendance_requests_id');

            // attendance_idカラムを再追加（元に戻すため）
            $table->unsignedBigInteger('attendance_id')->nullable();
            // 外部キー制約を再追加する場合
            $table->foreign('attendance_id')->references('id')->on('attendance_requests');
        });
    }
};
