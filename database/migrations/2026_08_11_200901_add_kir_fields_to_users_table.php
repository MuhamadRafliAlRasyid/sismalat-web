<?php
// database/migrations/xxxx_add_kir_fields_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 40)->nullable()->unique()->after('name');
            $table->string('jabatan', 100)->nullable()->after('nip');
            $table->string('gelar_depan', 20)->nullable()->after('jabatan');
            $table->string('gelar_belakang', 30)->nullable()->after('gelar_depan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'jabatan', 'gelar_depan', 'gelar_belakang']);
        });
    }
};
