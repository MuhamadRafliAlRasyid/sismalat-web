<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengembalian_alats', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembalian_alats', 'pengambilan_alat_item_id')) {
                $table->foreignId('pengambilan_alat_item_id')
                    ->nullable()
                    ->after('pengambilan_alat_id')
                    ->constrained('pengambilan_alat_items');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengembalian_alats', function (Blueprint $table) {
            if (Schema::hasColumn('pengembalian_alats', 'pengambilan_alat_item_id')) {
                $table->dropConstrainedForeignId('pengambilan_alat_item_id');
            }
        });
    }
};
