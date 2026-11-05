<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengambilan_alats', function (Blueprint $table) {
            $table->date('tanggal_jatuh_tempo')->nullable()->after('lama_pinjam');
            $table->boolean('notified_15_percent')->default(false)->after('status');
            $table->boolean('notified_1_day')->default(false)->after('notified_15_percent');
            $table->boolean('notified_overdue')->default(false)->after('notified_1_day');
        });
    }

    public function down(): void
    {
        Schema::table('pengambilan_alats', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_jatuh_tempo',
                'notified_15_percent',
                'notified_1_day',
                'notified_overdue',
            ]);
        });
    }
};
