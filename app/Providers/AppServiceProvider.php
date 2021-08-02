<?php

namespace App\Providers;

use App\Bitrix24\Bitrix24API;
use App\Imports\FieldsMapper;
use App\Interfaces\ProcessingImportIF;
use App\Jobs\ProcessUpdateEntityJob;
use App\Models\ProcessHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

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
         $this->app->singleton(Bitrix24API::class, function($app, $params ) {
             $bx24 = new Bitrix24API('https://b24-82kpks.bitrix24.ru/rest/1/vhz2n4j86zw2d7a9/');
             $bx24->http->throttle = 2; // не чаще раза в пол сек

             /*                // Устанавливаем каталог для сохранения лог файлов
                             DebugLogger::$logFileDir = storage_path('b24logs' . DIRECTORY_SEPARATOR);
                             // Создаем объект класса логгера
                             $logFileName = 'debug_bitrix24api.log';
                             $logger = DebugLogger::instance($logFileName);
                             // Включаем логирование
                             $logger->isActive = true;
                             // Устанавливаем логгер
                             $bx24->setLogger($logger);*/
             return $bx24;
         });
        $this->app->bind(ProcessingImportIF::class, function($app, $params ) {
            return ProcessHistory::where('processing', 1)->firstOrFail();
        });

        HeadingRowFormatter::extend('format_b24_fields', function($value, $key) {
           // Log::debug('jknjin6666666666666');
            return FieldsMapper::mapOne($value);
        });

 /*        $this->app->when(ProcessUpdateEntityJob::class)
            ->needs(ProcessHistory::class)
            ->give(function () {
                return ProcessHistory::where('processing', 1)->first();
            });*/



    }
}
