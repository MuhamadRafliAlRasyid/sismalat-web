<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengambilan_alats', function (Blueprint $table) {
            $table->foreignId('approved_by')
                  ->nullable()
                  ->after('is_critical')
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('ID admin/super yang menyetujui');

            $table->string('approved_by_name', 255)
                  ->nullable()
                  ->after('approved_by')
                  ->comment('Nama admin saat approval (snapshot)');

            $table->timestamp('approved_at')
                  ->nullable()
                  ->after('approved_by_name')
                  ->comment('Waktu persetujuan');

            $table->index(['approved_by', 'approved_at'], 'idx_pengambilan_approval');
        });
    }

    public function down(): void
    {
        Schema::table('pengambilan_alats', function (Blueprint $table) {
            $table->dropIndex('idx_pengambilan_approval');
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_at', 'approved_by_name', 'approved_by']);
        });
    }
};
