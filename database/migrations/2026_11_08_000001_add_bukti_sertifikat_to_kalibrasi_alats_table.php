<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('kalibrasi_alats', 'bukti_sertifikat')) {
            Schema::table('kalibrasi_alats', function (Blueprint $table) {
                $table->string('bukti_sertifikat')->nullable()->after('no_sertifikat');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('kalibrasi_alats', 'bukti_sertifikat')) {
            Schema::table('kalibrasi_alats', function (Blueprint $table) {
                $table->dropColumn('bukti_sertifikat');
            });
        }
    }
};
