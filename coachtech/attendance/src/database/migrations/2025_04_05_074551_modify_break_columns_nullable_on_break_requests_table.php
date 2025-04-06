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
            $table->unsignedBigInteger('break_id')->nullable()->change();
            $table->time('break_start')->nullable()->change();
            $table->time('break_end')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('break_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('break_id')->nullable(false)->change();
            $table->time('break_start')->nullable(false)->change();
            $table->time('break_end')->nullable(false)->change();
        });
    }
};
