<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengambilan_alats', function (Blueprint $table) {
            $table->string('approved_by_role', 40)->nullable()->after('approved_by_name');
            $table->string('verification_token', 64)->nullable()->unique()->after('approved_by_role');
        });

        // Beri token juga kepada transaksi yang sudah disetujui sebelum fitur QR dipasang.
        DB::table('pengambilan_alats')
            ->whereNotNull('approved_at')
            ->whereNull('verification_token')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('pengambilan_alats')
                        ->where('id', $row->id)
                        ->update([
                            'approved_by_role' => $row->approved_by_role ?? 'Admin / Super Admin',
                            'verification_token' => Str::random(64),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('pengambilan_alats', function (Blueprint $table) {
            $table->dropUnique(['verification_token']);
            $table->dropColumn(['verification_token', 'approved_by_role']);
        });
    }
};
