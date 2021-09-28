<?php

namespace Tests\Feature;

use App\Imports\EntityDataImport;
use App\Models\B24FieldsDictionary;
use App\Models\ProcessHistory;
use App\Services\Bitrix24ConcreteMethodFactory;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class TestUpload extends TestCase
{


    //public const USER_FIELDS_CONTACT_STRING = 'UF_CRM_1632337224644';
    // public const USER_FIELDS_CONTACT_STRING = 'UF_CRM_1632337224644';

    //public $b24_entity_id = Entity::COMPANY_ENTITY_ID;
    //public $b24_id = 17199;


    protected  $entityId, $b24Id;
    protected const FILES_ROOT = "storage/МРП - Тестовые файлы/";

    protected function setUp(): void
    {
        parent::setUp();
        ProcessHistory::truncate();
        if(!app()->runningUnitTests())
            throw new \Exception('wrong env, probably it needs to clear the cache');

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
            return new Request(['entity_id' => $this->entityId, 'b24ID' => $this->b24Id]);
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
    protected function assertB24FieldEquals( $expected,  $userFieldKey, $cast=false): void
    {
         $actual = (new Bitrix24ConcreteMethodFactory($this->entityId))
                            ->GetOne($this->b24Id)[$userFieldKey];


         if($cast) {
             if( is_array($actual)) {
                 foreach($actual as $key => $val)
                 {
                     $actual[$key] = $this->cast($val, $cast);
                 }
                 foreach($expected as $key => $val)
                 {
                     $expected[$key] = $this->cast($val, $cast);
                 }
             }  else {
                 $actual = $this->cast($actual, $cast);
                 $expected = $this->cast($expected, $cast);
             }
         }

        $this->assertEquals($expected, $actual);
    }

    function cast($val, $type) {
        switch($type) {
            case "date":
                return Carbon::parse($val)->toDateString();
                break;
            case "datetime":
                return Carbon::parse($val)->toDateTimeString();
                break;

            default:
        }
    }

    protected function assertUpdateLogContains(string $error_text): void
    {
        $this->assertNotFalse(
            strpos($this->getUpdateLog(), $error_text)
        );

    }

    protected function getUpdateLog(): string
    {
        return Storage::disk('log')->get('update.log');
    }





    /**
     * @throws BindingResolutionException
     */
    protected function value_is_valid($filename, $expected, $uf_human_name, $cast=false): void
    {
        Artisan::call('log:clear');
        $this->bindRequest();
        $this->import($filename);
        $this->assertProcessStarted();

        $this->assertB24FieldEquals(
            $expected, $this->getUFKey($uf_human_name), $cast
        );
    }


    /**
     * @throws BindingResolutionException
     */
    protected function is_cleared_when_empty($filename, $uf_human_name): void
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
    protected function error_log_contains_errormsg($filename, $error_text="обязательное поле"): void
    {
        Artisan::call('log:clear');
        $this->bindRequest();
        $this->import($filename);
        $this->assertProcessStarted();

        $this->assertUpdateLogContains($error_text);

    }

    protected function getUFKey(string $UF_HUMAN_NAME)
    {

/*        dd($UF_HUMAN_NAME, $this->entityId,
            B24FieldsDictionary::
            where('entity_id', $this->entityId)
                ->where('title', $UF_HUMAN_NAME)->get()
        );*/
        return B24FieldsDictionary::
        where('entity_id', $this->entityId)
            ->where('title', $UF_HUMAN_NAME)
            ->first()
            ->field_code;
    }






}
