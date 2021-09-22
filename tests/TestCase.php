<?php

namespace Tests;

use App\Bitrix24\Bitrix24API;
use App\Imports\EntityDataImport;
use App\Models\B24FieldsDictionary;
use App\Models\Entity;
use App\Models\ProcessHistory;
use App\Services\Bitrix24ConcreteMethodFactory;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;


abstract class TestCase extends BaseTestCase
{
    use CreatesApplication; // MigrateFreshSeedOnce

    public const UF_CRM_STRING = "ПолеСтрока";
    public const UF_CRM_STRING_MULTIPLE = "СтрокаМножств";
    public const UF_CRM_INTEGER = "Число222";
    public const CONTROL_STRING = "test est srgg";
    public const CONTROL_INTEGER = 12;
    public const CONTROL_DOUBLE = 15.34;
    public const CONTROL_STRING_MULTIPLE = ["qqqqqqqqqqqq"];
    public const CONTROL_STRING_MULTIPLE2 = ["vdfb 546", "выапап 54"];
    //public const USER_FIELDS_CONTACT_STRING = 'UF_CRM_1632337224644';
    // public const USER_FIELDS_CONTACT_STRING = 'UF_CRM_1632337224644';

    //public $b24_entity_id = Entity::COMPANY_ENTITY_ID;
    //public $b24_id = 17199;


    protected  $entityId, $b24Id;
    protected const FILES_ROOT = "/home/super/Downloads/МРП - Тестовые файлы/";

     protected function setUp(): void
    {
        parent::setUp();
        ProcessHistory::truncate();

        //$this->b24 = app()->make(Bitrix24API::class);

    }

    protected function import($filename): void
    {
        Excel::import(new EntityDataImport,
            new UploadedFile(self::FILES_ROOT . $filename, $filename)
        );

      //  sleep(1);
    }
    protected function bindRequest(): void
    {
        app()->bind('request', function ($app) {
            return new Request(['entity_id' => $this->entityId]);
        });
    }
    protected function assertProcessStarted(): void
    {
        $this->assertNotFalse(
            strpos(
                $this->getUpdateLog(),
                "Новый процесс запущен"
            )
        );
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    protected function assertB24FieldEquals( $expected,  $userFieldKey): void
    {
        $b24MethodFactory = new Bitrix24ConcreteMethodFactory($this->entityId);
        $this->assertEquals(
            $expected,
            $b24MethodFactory->GetOne($this->b24Id)[$userFieldKey]
        );
    }
    protected function assertUpdateLogContains(string $string): bool
    {
        return strpos($string, $this->getUpdateLog()) !== false;
    }

    protected function getUpdateLog(): string
    {
        return Storage::disk('log')->get('update.log');
    }





    /**
     * @throws BindingResolutionException
     */
    protected function value_is_valid($filename, $expected, $uf_human_name): void
    {
        Artisan::call('log:clear');
        $this->bindRequest();
        $this->import($filename);
        $this->assertProcessStarted();
        $this->assertB24FieldEquals(
            $expected, $this->getUFKey($uf_human_name)
        );
    }

    /**
     * @throws BindingResolutionException
     */
    protected function string_is_cleared_when_empty($filename, $uf_human_name): void
    {
        Artisan::call('log:clear');
        $this->bindRequest();
        $this->import($filename);
        $this->assertProcessStarted();
        $this->assertB24FieldEquals(
            "",
            $this->getUFKey($uf_human_name)
        );



    }
    protected function error_when_requred_is_empty($filename, $error_text="обязательное поле"): void
    {
        Artisan::call('log:clear');
        $this->bindRequest();
        $this->import($filename);
        $this->assertProcessStarted();
        $this->assertUpdateLogContains($error_text);

    }

    protected function getUFKey(string $UF_HUMAN_NAME)
    {
        return B24FieldsDictionary::where('title', $UF_HUMAN_NAME)
            ->first()
            ->field_code;
    }


}
