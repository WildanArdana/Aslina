<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            $table->time('office_start')->nullable()->after('shift2_end'); // Jam Masuk Kantor
            $table->time('office_end')->nullable()->after('office_start'); // Jam Pulang Kantor
        });
    }

    public function down(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            $table->dropColumn(['office_start', 'office_end']);
        });
    }
};