<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            $table->time('shift1_start')->nullable()->after('radius');
            $table->time('shift2_start')->nullable()->after('shift1_start');
        });
    }

    public function down(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            $table->dropColumn(['shift1_start', 'shift2_start']);
        });
    }
};