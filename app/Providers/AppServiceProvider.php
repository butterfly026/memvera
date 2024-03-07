<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Relation::morphMap([
            'leads'         => 'App\DataStructure\Lead\Models\Lead',
            'products'      => 'App\DataStructure\Product\Models\Product',
            'persons'       => 'App\DataStructure\Contact\Models\Person',
            'organizations' => 'App\DataStructure\Contact\Models\Organization',
            'quotes'        => 'App\DataStructure\Quote\Models\Quote',
        ]);
        $this->app->register(EventServiceProvider::class);
    }
}
