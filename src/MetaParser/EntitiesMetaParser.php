<?php

namespace Kodix\LaravelHelpers\MetaParser;

class EntitiesMetaParser
{
    /**
     * @var string|array
     */
    private $meta;

    /**
     * @var MetaParseableInterface
     */
    private $parseable;

    private $asArray = false;

    private $keys = [];

    private $values = [];

    public function __construct(MetaParseableInterface $parseable, $meta)
    {
        $this->parseable = $parseable;
        if (is_array($meta)) {
            $this->meta = $meta;
            $this->asArray = true;
        } else {
            $this->meta = [$meta];
        }

        $this->prepareParseable();
    }

    protected function prepareParseable()
    {
        $attributes = $this->parseable->getMetaParseableData();

        $this->keys = array_map(function ($key) {
            return "{{{$key}}}";
        }, array_keys($attributes));

        $this->values = array_values($attributes);
    }

    public function parseString(string $string)
    {
        return str_replace($this->keys, $this->values, $string);
    }

    /**
     * @return array|mixed
     */
    public function parse()
    {
        $value = array_map([$this, 'parseString'], $this->meta);

        return $this->asArray ? $value : reset($value);
    }
}
