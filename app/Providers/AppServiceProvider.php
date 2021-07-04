<?php

namespace App\Providers;

use App\Bitrix24\Bitrix24API;
use App\Interfaces\ProcessingImportIF;
use App\Jobs\ProcessUpdateEntityJob;
use App\Models\ProcessHistory;
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
        $this->app->bind(ProcessingImportIF::class, function($app, $params ) {
            return ProcessHistory::where('processing', 1)->firstOrFail();
        });

/*        $this->app->when(ProcessUpdateEntityJob::class)
            ->needs(ProcessHistory::class)
            ->give(function () {
                return ProcessHistory::where('processing', 1)->first();
            });*/



    }
}
