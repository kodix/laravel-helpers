<?php

namespace Kodix\LaravelHelpers\MetaParser;

interface MetaParseableInterface
{
    /**
     * Must return an array of possible attributes and values to parse and replace.
     *
     * @return mixed
     */
    public function getMetaParseableData();
}
