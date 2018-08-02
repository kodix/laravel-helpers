<?php
/**
 * This file is a part of Yamaha Web CIS project.
 * Email:       support@kodix.ru
 * Company:     Kodix LLC <https://kodix.com>
 * Developer:   Igor Malyuk <https://github.com/malyusha>
 */

namespace Kodix\LaravelHelpers\Traits;

trait HasHierarchy
{
    /**
     * Выводит атрибуты по иерархии. Подразумевается, что при использовании данного трейта в классе
     * будет подключена библиотека NestedSet.
     *
     * @param string $attribute
     * @param string|bool $delimiter
     * @param bool $reversed
     *
     * @return string|\Illuminate\Support\Collection
     */
    public function getHierarchy($attribute = 'name', $delimiter = ' --> ', $reversed = false)
    {
        $valueRetriever = ! is_callable($attribute) ? function ($entity) use ($attribute) {
            return $entity->$attribute;
        } : $attribute;

        $selfValue = $valueRetriever($this->entity);

        $collection = $this->ancestors->isEmpty() ? collect([$selfValue]) : $this->ancestors->map($valueRetriever)
            ->push($selfValue);

        if (is_bool($delimiter)) {
            return $collection;
        }

        if ($reversed) {
            $collection = $collection->reverse();
        }

        return is_string($delimiter) ? $collection->implode($delimiter) : $collection->map($delimiter)
            ->implode('');
    }
}