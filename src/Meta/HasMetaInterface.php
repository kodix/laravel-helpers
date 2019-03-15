<?php

namespace Kodix\LaravelHelpers\Meta;

interface HasMetaInterface
{
    /**
     * Получает meta  атрибуты в виде массива.
     *
     * @return array
     */
    public function getMeta(): array;
}
