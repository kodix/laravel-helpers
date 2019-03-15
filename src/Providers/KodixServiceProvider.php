<?php

namespace Kodix\LaravelHelpers\Providers;

use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;

class KodixServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerBlueprintMacros();
    }

    protected function registerBlueprintMacros(): void
    {
        Blueprint::macro('weight', function ($defaultOrder = 10) {
            /**@var Blueprint $this */
            return $this->integer('weight')->nullable()->default($defaultOrder);
        });

        Blueprint::macro('meta', function () {
            /**@var Blueprint $this */
            return $this->jsonb('meta')->default('{}');
        });

        Blueprint::macro('uuid4', function ($columnName) {
            // WARNING! THIS WILL ONLY WORK IN POSTGRES WITH INSTALLED EXTENSION `uuid-ossp`
            /**@var Blueprint $this */
            return $this->uuid($columnName)->default(new Expression('uuid_generate_v4()'));
        });
    }
}
