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

class EntityUploadTest extends TestUpload
{


    protected $b24Id = 1261;
    protected $entityId = Entity::CONTACT_ENTITY_ID;


    ######################################
    # STRING
    public function test_string_valid(): void
    {

        $this->value_is_valid(
            "тест строка валидная.xlsx",
            self::CONTROL_STRING,
             self::UF_CRM_STRING
        );

    }
    public function test_string_multiple_valid(): void
    {
        $this->value_is_valid(
            "тест множественная строка.xlsx",
            static::CONTROL_STRING_MULTIPLE,
            self::UF_CRM_STRING_MULTIPLE );

    }
    public function test_string_multiple_valid2(): void
    {
        $this->value_is_valid(
            "тест множественная строка2.xlsx",
            static::CONTROL_STRING_MULTIPLE2,
            self::UF_CRM_STRING_MULTIPLE );

    }
    public function test_string_multiple_cleared(): void
    {
        $this->value_is_valid(
            "тест множественная строка очистка.xlsx",
            [],
            self::UF_CRM_STRING_MULTIPLE );

    }
    public function test_string_cleared_when_empty(): void
    {
        $this->is_cleared_when_empty(
            "очищает необяз строку при пустом значении.xlsx",
            self::UF_CRM_STRING
        );

    }
    public function test_error_when_required_string_is_empty(): void
    {
        $this->error_when_required_is_empty("ошибка при пустой обязат. строке.xlsx");

    }
    public function test_error_when_required_string_multiple_is_empty(): void
    {
        $this->error_when_required_is_empty("ошибка при пустой обязат. множ. строке.xlsx");
    }


    ######################################
    # INTEGER

    public function test_integer_valid(): void
    {
        $this->value_is_valid(
            "тест число валидное.xlsx",
            self::CONTROL_INTEGER,
            self::UF_CRM_INTEGER);

    }
    public function test_double_valid(): void
    {
        $this->value_is_valid(
            "тест число десятичн. валидное.xlsx",
            self::CONTROL_DOUBLE,
            self::UF_CRM_INTEGER);

    }

    public function test_int_multiple_valid(): void
    {
        $this->value_is_valid(
            "тест множ число валид.xlsx",
            static::CONTROL_INTEGER_MULTIPLE,
            self::UF_CRM_INTEGER_MULTIPLE );

    }


    public function test_int_multiple_valid2(): void
    {
        $this->value_is_valid(
            "тест множ число валид2.xlsx",
            static::CONTROL_INTEGER_MULTIPLE2,
            self::UF_CRM_INTEGER_MULTIPLE );

    }


    public function test_int_cleared_when_empty(): void
    {
        $this->is_cleared_when_empty(
            "очищает необяз число при пустом значении.xlsx",
            self::UF_CRM_INTEGER
        );

    }


    public function test_int_multiple_cleared(): void
    {
        $this->value_is_valid(
            "тест множественное число очистка.xlsx",
            [],
            self::UF_CRM_INTEGER_MULTIPLE );

    }
    public function test_error_when_int_is_not_valid(): void
    {
        $this->error_when_required_is_empty("ошибка невалидное число.xlsx", "не соответствует типу");

    }
    public function test_error_when_int_multiple_not_valid(): void
    {
        $this->error_when_required_is_empty("ошибка невалидное множ. число.xlsx", "не соответствует типу");

    }

    public function test_error_when_required_int_is_empty(): void
    {
        $this->error_when_required_is_empty("ошибка при пустой обязат. число.xlsx");

    }
    public function test_error_when_required_int_multiple_is_empty(): void
    {
        $this->error_when_required_is_empty("ошибка при пустой об   язат. множ. число.xlsx");
    }


    ######################################
    # DATE,DATETIME

    public function test_date_valid(): void
    {
        $this->value_is_valid(
            "тест дата.xlsx",
            self::CONTROL_DATE,
            self::UF_CRM_DATE,
                'date');

    }

    public function test_datetime_valid(): void
    {
        $this->value_is_valid(
            "тест датавремя2.xlsx",
            self::CONTROL_DATETIME,
            self::UF_CRM_DATETIME,
                'datetime');

    }

    public function test_date_multiple_valid(): void
    {
        $this->value_is_valid(
            "тест дата множ.xlsx",
            ["01.01.2021","05.10.2020"],
            self::UF_CRM_DATE_MULTIPLE,
                'date');

    }
    public function test_datetime_multiple_valid(): void
    {
        $this->value_is_valid(
            "тест дата множ2.xlsx",
            ["01.01.2021 12:23:23","05.10.2020 10:09:15"],
            self::UF_CRM_DATETIME_MULTIPLE,
            'datetime');

    }
    public function test_date_cleared_when_empty(): void
    {
        $this->is_cleared_when_empty(
            "тест дата очистка.xlsx",
            self::UF_CRM_DATE
        );

    }
    public function test_datetime_cleared_when_empty(): void
    {
        $this->is_cleared_when_empty(
            "тест датавремя2 пустое очистка.xlsx",
            self::UF_CRM_DATETIME
        );

    }
    public function test_datetime_multiple_cleared_when_empty(): void
    {
        $this->value_is_valid(
            "тест дата множ очистка.xlsx",
            [],
            self::UF_CRM_DATETIME_MULTIPLE
        );

    }
    public function test_error_when_date_is_not_valid(): void
    {
        $this->error_when_required_is_empty("тест дата невалидная.xlsx", "не соответствует типу");

    }
    public function test_error_when_datetime_is_not_valid(): void
    {
        $this->error_when_required_is_empty("тест датавремя2 невалидная.xlsx", "не соответствует типу");

    }
    public function test_error_when_date_multiple_is_not_valid(): void
    {
        $this->error_when_required_is_empty("тест дата множ невалид.xlsx", "не соответствует типу");

    }
    public function test_error_when_one_of_date_multiple_is_not_valid(): void
    {
        $this->error_when_required_is_empty("тест дата множ c невалидным значением.xlsx", "не соответствует типу");

    }

    public function test_date_require(): void
    {
        $this->error_when_required_is_empty("тест дата обязательная с пустым значением.xlsx");

    }
    public function test_date_multiple_require(): void
    {
        $this->error_when_required_is_empty("тест дата обязательная множ с пустым значением.xlsx");

    }


    ######################################
    # BOOLEAN

    public function test_boolean_valid(): void
    {
        $this->value_is_valid(
            "тест данет.xlsx",
            "1",
            "Булево");

    }



    public function test_boolean_cleared_when_empty(): void
    {
        $this->value_is_valid(
            "тест данет пустое.xlsx",
            "0",
            "Булево");

    }

     public function test_boolean_not_valid(): void
    {
        $this->error_when_required_is_empty("тест данет невалидный.xlsx", "не соответствует типу");

    }






}
