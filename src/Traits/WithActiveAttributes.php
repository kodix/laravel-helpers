<?php
/**
 * This file is a part of Yamaha Web CIS project.
 * Email:       support@kodix.ru
 * Company:     Kodix LLC <https://kodix.com>
 * Developer:   Igor Malyuk <https://github.com/malyusha>
 */

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
