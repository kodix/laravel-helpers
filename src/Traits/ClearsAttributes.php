<?php
/**
 * This file is a part of Yamaha Web CIS project.
 * Email:       support@kodix.ru
 * Company:     Kodix LLC <https://kodix.com>
 * Developer:   Igor Malyuk <https://github.com/malyusha>
 */

namespace Kodix\LaravelHelpers\Traits;

/**
 * Данный трейт очищает значение атрибута модели от html тегов.
 *
 * Trait ClearsAttributes
 */
trait ClearsAttributes
{
    /**
     * Очищает атрибут от html тегов.
     *
     * @param string $attribute
     *
     * @return string
     */
    public function clearAttribute(string $attribute): string
    {
        return strip_tags($this->attributes[$attribute]);
    }
}