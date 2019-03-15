<?php

namespace Kodix\LaravelHelpers\Support\Database\Seed;


trait ModelGuardian
{
    /**
     * Reguards models.
     */
    public static function reguard()
    {
        foreach (static::getGuardedModels() as $model) {
            $model::reguard();
        }
    }
    /**
     * Unguards models.
     *
     */
    public static function unguard()
    {
        foreach (static::getGuardedModels() as $model) {
            $model::unguard();
        }
    }
    /**
     * Retrieves arguments.
     *
     * @return array
     */
    protected static function getGuardedModels() :array
    {
        return property_exists(get_called_class(), 'guardedModels') ? static::$guardedModels : [];
    }
}
