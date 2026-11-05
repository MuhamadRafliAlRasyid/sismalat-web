
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengambilan_alats', function (Blueprint $table) {
            $table->dropForeign('pengambilan_alats_user_id_foreign');
            $table->dropForeign('pengambilan_alats_bagian_id_foreign');
        });

        Schema::table('pengambilan_alats', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('bagian_id')->nullable()->change();

            $table->string('tipe_peminjam', 20)
                ->default('internal')
                ->after('nama_peminjam');

            $table->string('email_peminjam')->nullable();
            $table->string('telepon_peminjam', 30)->nullable();
            $table->string('instansi_peminjam')->nullable();

            $table->string('kanal_notifikasi', 20)
                ->default('email');

            $table->char('tracking_token_hash', 64)
                ->nullable()
                ->unique();
        });

        Schema::table('pengambilan_alats', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('bagian_id')
                ->references('id')->on('bagian')
                ->nullOnDelete();
        });

        Schema::table('pengembalian_alats', function (Blueprint $table) {
            $table->dropForeign(
                'pengembalian_alats_user_id_foreign'
            );
        });

        Schema::table('pengembalian_alats', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')
                ->nullable()
                ->change();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Jangan rollback otomatis jika sudah ada pinjaman publik.
        // Data peminjam publik perlu dipindahkan terlebih dahulu.
        throw new RuntimeException(
            'Rollback manual diperlukan agar data pinjaman publik tidak hilang.'
        );
    }
};
