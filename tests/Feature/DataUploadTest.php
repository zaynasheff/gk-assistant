<?php

namespace Tests\Feature;

use App\Bitrix24\Bitrix24API;
use App\Imports\EntityDataImport;
use App\Models\B24FieldsDictionary;
use App\Models\Entity;
use App\Models\ProcessHistory;
use App\Services\Bitrix24ConcreteMethodFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class DataUploadTest extends TestCase
{

    /**
     * A basic feature test example.
     * ПолеДеньги
     * @return void
     */
    public function test_money_valid_99() : void
    {
        Artisan::call('log:clear');
        //app()->detectEnvironment(function() { return 'testing'; });
        //dd(app()->environment(), \DB::connection()->getDatabaseName());
        app()->bind('request', function ($app) {
            return new Request(['entity_id' => $this->b24_entity_id]);
        });

        $path = "/home/super/Downloads/МРП - Тестовые файлы/тест деньги.xlsx";
        $file = new UploadedFile($path, "test.xlsx");

        Excel::import(new EntityDataImport, $file);
        sleep(2);
        $this->assertNotFalse(
            strpos(
                Storage::disk('log')->get('update.log'),
                "Новый процесс запущен"
            )
        );

        $b24 = app()->make(Bitrix24API::class);
        $b24MethodFactory = new Bitrix24ConcreteMethodFactory($this->b24_entity_id);
        $this->assertEquals(
            "99|RUB",
            $b24MethodFactory->GetOne($this->b24_id)["UF_CRM_1630915960694"]
        );

    }
}
