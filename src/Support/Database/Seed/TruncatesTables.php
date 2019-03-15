<?php

namespace Kodix\LaravelHelpers\Support\Database\Seed;

use DB;

/**
 * Trait TruncateTables
 *
 * @package App\Traits
 */
trait TruncatesTables
{
    /**
     * Очищает переданные таблицы через truncate.
     *
     * @param $tables
     */
    public function truncate($tables): void
    {
        $tables = is_array($tables) ? $tables : func_get_args();
        foreach ($tables as $table) {
            DB::unprepared("TRUNCATE ONLY {$table} RESTART IDENTITY CASCADE");
        }
    }
}
