<?php

namespace Kodix\LaravelHelpers\Traits;

use ReflectionClass;

trait EnumTranslatable
{
    protected static $constants = [];

    protected static $translationMap;

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getConstants(): array
    {
        if (! empty(static::$constants)) {
            return static::$constants;
        }

        $class = new ReflectionClass(static::class);

        return static::$constants = collect($class->getConstants())->filter(function ($constant) {
            return ! \in_array($constant, ['created_at', 'updated_at']);
        })->all();
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function has(string $key): bool
    {
        return \in_array($key, static::getConstants(), true);
    }

    /**
     * Получает массив в котором константы данного enum поля являются ключами, а значение -
     * переводы для языка из файлов.
     *
     * @param array $without
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function getTranslationMap(array $without = []): array
    {
        if (static::$translationMap) {
            return static::$translationMap;
        }

        $map = [];
        $transKey = static::getEnumTranslationKey();
        foreach (static::getConstants() as $value => $constant) {
            if (\in_array($constant, $without, true)) {
                continue;
            }
            $map[$constant] = trans("enums.{$transKey}.{$constant}");
        }

        return static::$translationMap = $map;
    }

    /**
     * @return array
     */
    public static function getTranslationCollection(): array
    {
        $collection = [];
        $translations = static::getTranslationMap();

        foreach ($translations as $type => $name) {
            $collection[] = compact('type', 'name');
        }

        return $collection;
    }

    /**
     * Возвращает ключ массива для переводов каждого значения.
     *
     * @return string
     */
    abstract public static function getEnumTranslationKey(): string;
}
