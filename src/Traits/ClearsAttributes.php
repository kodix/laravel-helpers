<?php

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
