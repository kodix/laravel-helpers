<?php

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
