<?php

namespace Kodix\LaravelHelpers\Meta;

use Illuminate\Support\Arr;

trait HasMeta
{
    protected $cachedMetaModifiers;

    protected $cachedNeedsTranslation;

    protected $modifiedAttributes = [];

    /**
     * @param string|array $name
     * @param string $value
     *
     * @return static
     */
    public function setMeta($name, $value = null)
    {
        $this->initializeMeta();

        if (! is_array($name) && $value !== null) {
            $meta = [$name => $value];
        } else {
            $meta = $name;
        }

        foreach ($meta as $attribute => $content) {
            if ($content) {
                Arr::set($this->meta, $attribute, $content);
            }
        }

        return $this;
    }

    protected function getMetaModifiers()
    {
        if (property_exists($this, 'modifyMeta') && $this->modifyMeta === false) {
            return [];
        }

        return property_exists($this, 'metaModifiers') ? array_diff($this->metaModifiers, $this->modifiedAttributes) : [];
    }

    /**
     * Возвращает meta данные по ключу. Если ключ опущен, то возвращает все meta-данные.
     *
     * @param $key
     *
     * @param bool $useModifier
     *
     * @return mixed
     */
    public function getMeta($key = null, $useModifier = true)
    {
        $this->initializeMeta();
        if (is_bool($key) || $key === null) {
            return collect((array) $this->meta)->map(function ($value, $attribute) use ($key) {
                return $this->getMetaValue($attribute, $value, $key === null || $key === true ? true : false);
            })->all();
        }

        return $this->getMetaValue($key, null, $useModifier);
    }

    /**
     * @param $key
     * @param $value
     *
     * @param bool $useModifier
     *
     * @return array|\Illuminate\Contracts\Translation\Translator|null|string
     */
    protected function getMetaValue($key, $value = null, $useModifier = true)
    {
        $value = $value ?? Arr::get((array) $this->meta, $key);

        if (! $value) {
            return null;
        }

        $modifier = 'defaultModifier';
        if ($this->cachedNeedsTranslation === null) {
            $this->cachedNeedsTranslation = (property_exists($this, 'metaNeedsTranslation') && $this->metaNeedsTranslation);
        }

        if ($useModifier && in_array($key, $this->getMetaModifiers(), true)) {
            $modifier = 'modify'.ucfirst($key);
            $this->modifiedAttributes[] = $key;
        }

        return $this->{$modifier}($this->cachedNeedsTranslation ? trans($value) : $value);
    }

    protected function defaultModifier($value)
    {
        return $value;
    }

    /**
     * Устанавливает свойство названия.
     *
     * @param string $value
     *
     * @param bool $setPageTitle
     *
     * @return $this
     */
    public function setTitle(string $value, bool $setPageTitle = true)
    {
        if ($setPageTitle) {
            $this->setPageTitle($value);
        }

        return $this->setMeta('title', $value);
    }

    public function setPageTitle(string $value)
    {
        return $this->setMeta('pageTitle', $value);
    }

    /**
     * Устанавливает свойство описания.
     *
     * @param string $value
     *
     * @return $this
     */
    public function setDescription(string $value)
    {
        return $this->setMeta('description', $value);
    }

    /**
     * Устанавливает свойство keywords.
     *
     * @param string|array $keywords
     *
     * @return $this
     */
    public function setKeywords($keywords)
    {
        $keywords = is_array($keywords) ? $keywords : func_get_args();

        return $this->setMeta('keywords', implode(', ', $keywords));
    }

    /**
     * Инициализирует мета-данные массивом, если они еще не инициализированны.
     */
    protected function initializeMeta()
    {
        if (! property_exists($this, 'meta') || ! is_array($this->meta)) {
            $this->meta = [];
        }
    }

    /**
     * Инициализирует мета-данные из переданного объекта MetaContract.
     *
     * @param \Kodix\LaravelHelpers\Meta\HasMetaInterface $model
     */
    public function initializeMetaFromEntity(HasMetaInterface $model)
    {
        $this->setMeta($model->getMeta());
    }
}
