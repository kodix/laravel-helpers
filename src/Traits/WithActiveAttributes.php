<?php

namespace Kodix\LaravelHelpers\Traits;

trait WithActiveAttributes
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $builder)
    {
        $builder->where('is_active', true);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopePublished(\Illuminate\Database\Eloquent\Builder $builder)
    {
        $builder->where(function ($query) {
            $query->whereNotNull('published_at')->where('published_at', '<=', \Illuminate\Support\Carbon::now());
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeActiveAndPublished(\Illuminate\Database\Eloquent\Builder $builder)
    {
        $builder->active()->published();
    }
}
