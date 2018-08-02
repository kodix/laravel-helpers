<?php

namespace Kodix\LaravelHelpers\MetaParser;

trait HasDynamicMeta
{
    public function getDynamicMeta(string $key = null): array
    {
        $key = $key ?? static::class;
        $config = config("project.entities_meta.{$key}", []);

        return (new EntitiesMetaParser($this, $config))->parse();
    }
}
