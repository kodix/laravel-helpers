<?php
/**
 * This file is a part of Yamaha Web CIS project.
 * Email:       support@kodix.ru
 * Company:     Kodix LLC <https://kodix.com>
 * Developer:   Igor Malyuk <https://github.com/malyusha>
 */

namespace Kodix\LaravelHelpers\Support\Database\Seed;

use Illuminate\Database\Seeder as BaseSeeder;

abstract class Seeder extends BaseSeeder
{
    use TruncatesTables, ModelGuardian;

    protected static $guardedModels = [];

    protected static $noTruncate = false;

    /**
     * Runs prepared seeder.
     */
    public function run()
    {
        if (! static::$noTruncate) {
            $this->truncateModelsTables();
        }

        static::unguard();
        $this->seed();
        static::reguard();

    }

    /**
     * Truncates models tables.
     */
    protected function truncateModelsTables(): void
    {
        $tables = [];
        foreach (static::$guardedModels as $model) {
            $tables[] = (new $model)->getTable();
        }
        $this->truncate($tables);
    }

    abstract public function seed();
}
