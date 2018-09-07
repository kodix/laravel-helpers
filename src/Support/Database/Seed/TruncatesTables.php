<?php
/**
 * This file is a part of Yamaha Web CIS project.
 * Email:       support@kodix.ru
 * Company:     Kodix LLC <https://kodix.com>
 * Developer:   Igor Malyuk <https://github.com/malyusha>
 */

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
