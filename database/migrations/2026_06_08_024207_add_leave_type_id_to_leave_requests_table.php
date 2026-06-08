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
        Schema::table('leave_requests', function (Blueprint $table) {
            // Menambahkan relasi ke tabel jenis cuti
            $table->foreignId('leave_type_id')
                  ->nullable()
                  ->after('employee_id')
                  ->constrained('leave_types')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Menghapus foreign key dan kolom saat di-rollback
            $table->dropForeign(['leave_type_id']);
            $table->dropColumn('leave_type_id');
        });
    }
};