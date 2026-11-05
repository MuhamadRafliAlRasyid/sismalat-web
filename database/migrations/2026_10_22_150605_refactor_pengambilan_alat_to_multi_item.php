<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* ==========================================================
         | LANGKAH 0 — AMANKAN NILAI STATUS (pencegah error 1265)
         ========================================================== */
        // Longgarkan kolom ke VARCHAR sementara agar nilai lama
        // apa pun tidak ter-truncate saat dikonversi.
        DB::statement("ALTER TABLE pengambilan_alats MODIFY COLUMN status VARCHAR(30) NULL");

        // Normalisasi nilai lama → nilai canonical.
        // Sesuaikan daftar ini jika hasil query diagnostik Anda berbeda.
        DB::statement("UPDATE pengambilan_alats SET status = 'dikembalikan' WHERE status IN ('selesai', 'returned', 'kembali', 'closed')");
        DB::statement("UPDATE pengambilan_alats SET status = 'dipinjam' WHERE status IS NULL OR status NOT IN ('dipinjam', 'dikembalikan', 'sebagian_dikembalikan')");

        /* ==========================================================
         | LANGKAH 1 — TABEL ITEMS (idempotent: skip jika sudah ada)
         ========================================================== */
        if (!Schema::hasTable('pengambilan_alat_items')) {
            Schema::create('pengambilan_alat_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengambilan_alat_id')
                      ->constrained('pengambilan_alats')
                      ->cascadeOnDelete();
                $table->foreignId('alat_id')->constrained('alats')->cascadeOnDelete();
                $table->integer('jumlah');
                $table->string('satuan', 20)->default('unit');
                $table->enum('status', ['dipinjam', 'dikembalikan'])->default('dipinjam');
                $table->dateTime('waktu_pengembalian')->nullable();
                $table->string('foto_pengembalian')->nullable();
                $table->text('catatan_pengembalian')->nullable();
                $table->timestamps();

                $table->index(['pengambilan_alat_id', 'status']);
            });
        }

        /* ==========================================================
         | LANGKAH 2 — MIGRASI DATA (hanya jika kolom lama masih ada)
         ========================================================== */
        if (Schema::hasColumn('pengambilan_alats', 'alat_id')) {
            // Drop foreign key alat_id jika ada (cek via information_schema)
            $fks = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'pengambilan_alats'
                  AND COLUMN_NAME = 'alat_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            foreach ($fks as $fk) {
                DB::statement("ALTER TABLE pengambilan_alats DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }

            // Migrasi data yang BELUM ada di items (cegah duplikat saat re-run)
            $rows = DB::table('pengambilan_alats')->whereNotNull('alat_id')->get();
            foreach ($rows as $p) {
                $exists = DB::table('pengambilan_alat_items')
                    ->where('pengambilan_alat_id', $p->id)
                    ->where('alat_id', $p->alat_id)
                    ->exists();

                if (!$exists) {
                    DB::table('pengambilan_alat_items')->insert([
                        'pengambilan_alat_id' => $p->id,
                        'alat_id'             => $p->alat_id,
                        'jumlah'              => $p->jumlah ?? 1,
                        'satuan'              => $p->satuan ?? 'unit',
                        'status'              => ($p->status === 'dikembalikan') ? 'dikembalikan' : 'dipinjam',
                        'created_at'          => $p->created_at ?? now(),
                        'updated_at'          => $p->updated_at ?? now(),
                    ]);
                }
            }

            // Drop kolom lama
            Schema::table('pengambilan_alats', function (Blueprint $table) {
                $table->dropColumn(['alat_id', 'jumlah', 'satuan']);
            });
        }

        /* ==========================================================
         | LANGKAH 3 — KETATKAN STATUS KE ENUM BARU (aman sekarang)
         ========================================================== */
        $col = DB::select("SHOW COLUMNS FROM pengambilan_alats LIKE 'status'")[0] ?? null;

        if ($col && stripos($col->Type, 'sebagian_dikembalikan') === false) {
            DB::statement("ALTER TABLE pengambilan_alats MODIFY COLUMN status ENUM('dipinjam','sebagian_dikembalikan','dikembalikan') NOT NULL DEFAULT 'dipinjam'");
        }
    }

    public function down(): void
    {
        // Kembalikan kolom lama jika belum ada
        if (!Schema::hasColumn('pengambilan_alats', 'alat_id')) {
            Schema::table('pengambilan_alats', function (Blueprint $table) {
                $table->unsignedBigInteger('alat_id')->nullable()->after('nama_peminjam');
                $table->integer('jumlah')->nullable()->after('alat_id');
                $table->string('satuan', 20)->nullable()->after('jumlah');
            });
        }

        // Isi dari item pertama per header
        $items = DB::table('pengambilan_alat_items')->get();
        foreach ($items as $item) {
            DB::table('pengambilan_alats')
                ->where('id', $item->pengambilan_alat_id)
                ->whereNull('alat_id')
                ->update([
                    'alat_id' => $item->alat_id,
                    'jumlah'  => $item->jumlah,
                    'satuan'  => $item->satuan,
                ]);
        }

        Schema::dropIfExists('pengambilan_alat_items');

        // Kembalikan enum lama dengan aman
        DB::statement("ALTER TABLE pengambilan_alats MODIFY COLUMN status VARCHAR(30) NULL");
        DB::statement("UPDATE pengambilan_alats SET status = 'dikembalikan' WHERE status = 'sebagian_dikembalikan'");
        DB::statement("ALTER TABLE pengambilan_alats MODIFY COLUMN status ENUM('dipinjam','dikembalikan') NOT NULL DEFAULT 'dipinjam'");
    }
};
