<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pengambilan_alats', 'warning_overdue_sent_at')) {
            Schema::table('pengambilan_alats', function (Blueprint $table) {
                $table->timestamp('warning_overdue_sent_at')
                    ->nullable()
                    ->after('warning_1day_sent_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pengambilan_alats', 'warning_overdue_sent_at')) {
            Schema::table('pengambilan_alats', function (Blueprint $table) {
                $table->dropColumn('warning_overdue_sent_at');
            });
        }
    }
};
