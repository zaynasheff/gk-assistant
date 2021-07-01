<?php

namespace App\Providers;

use App\Bitrix24\Bitrix24API;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
         $this->app->bind(Bitrix24API::class, function($app, $params ) {
              return new Bitrix24API('https://b24-d32s2s.bitrix24.ru/rest/1/22qjwux2xfbo6ubk/');
         });

    }
}
