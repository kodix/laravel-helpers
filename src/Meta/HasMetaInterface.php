<?php
/**
 * This file is a part of Yamaha Web CIS project.
 * Email:       support@kodix.ru
 * Company:     Kodix LLC <https://kodix.com>
 * Developer:   Igor Malyuk <https://github.com/malyusha>
 */

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