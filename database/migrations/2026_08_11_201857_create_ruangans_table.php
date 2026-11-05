<?php
// database/migrations/xxxx_create_ruangans_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ruangans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ruangan', 20)->unique();       // C1, C3, C11, dll
            $table->string('nama_ruangan', 100)->nullable();    // Opsional: "Ruang Kepala"
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();                          // PJ ruangan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruangans');
    }
};
