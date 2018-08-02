<?php

namespace Kodix\LaravelHelpers\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as BaseCollection;

trait IdParser
{
    /**
     * Получает все id из переданного значения.
     *
     * @param  mixed $value
     *
     * @return mixed
     */
    public static function parseId($value)
    {
        if ($value instanceof Model) {
            return $value->getKey();
        }

        if ($value instanceof Collection) {
            return $value->modelKeys();
        }

        if ($value instanceof BaseCollection) {
            return $value->toArray();
        }

        return $value;
    }
}