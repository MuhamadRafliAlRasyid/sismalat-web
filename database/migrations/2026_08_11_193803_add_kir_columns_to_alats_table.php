<?php
// database/migrations/xxxx_add_kir_columns_to_alats_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alats', function (Blueprint $table) {
            // Lokasi barang — inti fitur "barang ada di ruangan mana?"
            $table->foreignId('ruangan_id')->nullable()->after('kategori_id')
                  ->constrained('ruangans')->nullOnDelete();

            // Detail sesuai kolom KIR
            $table->string('ukuran', 100)->nullable()->after('kapasitas'); // "1 BIRO", "20 kg"
            $table->string('satuan', 50)->default('unit')->after('jumlah'); // unit / set

            // Rincian kondisi (B / KB / RB)
            $table->unsignedInteger('jumlah_baik')->default(0)->after('satuan');
            $table->unsignedInteger('jumlah_kurang_baik')->default(0)->after('jumlah_baik');
            $table->unsignedInteger('jumlah_rusak_berat')->default(0)->after('jumlah_kurang_baik');

            // Kolom KET. di KIR (tahun)
            $table->year('tahun_pengadaan')->nullable()->after('jumlah_rusak_berat');

            // Percepat scheduler notifikasi kalibrasi
            $table->index('masa_berlaku');
            // Opsional (MySQL 8): pencarian cepat
            // $table->fullText(['nama_alat', 'merk', 'tipe']);
        });
    }

    public function down(): void
    {
        Schema::table('alats', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ruangan_id');
            $table->dropIndex(['masa_berlaku']);
            $table->dropColumn(['ukuran', 'satuan', 'jumlah_baik',
                                'jumlah_kurang_baik', 'jumlah_rusak_berat', 'tahun_pengadaan']);
        });
    }
};
