<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Schema;

trait ChecksSchemaColumns
{
    /** Cache hasil cek kolom per request (hindari query information_schema berulang) */
    protected array $colCache = [];

    protected function hasCol(string $table, string $column): bool
    {
        $key = $table . '@' . $column;
        return $this->colCache[$key] ??= Schema::hasColumn($table, $column);
    }

    /** Kembalikan hanya kolom yang benar-benar ada di tabel */
    protected function existingCols(string $table, array $columns): array
    {
        return array_values(array_filter($columns, fn ($c) => $this->hasCol($table, $c)));
    }
}
