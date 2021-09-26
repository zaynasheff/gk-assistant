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

class ContactUploadTest extends TestUpload
{

    public const UF_CRM_STRING = "ПолеСтрока";
    public const UF_CRM_STRING_MULTIPLE = "СтрокаМнож";
    public const UF_CRM_INTEGER = "Число";
    public const CONTROL_STRING = "test est srgg";
    public const CONTROL_INTEGER = 12;
    public const UF_CRM_INTEGER_MULTIPLE = "ЧислоМнож";
    public const CONTROL_INTEGER_MULTIPLE = [567, 11.23, 51];
    public const CONTROL_INTEGER_MULTIPLE2 = [32];
    public const CONTROL_DOUBLE = 15.34;
    public const CONTROL_STRING_MULTIPLE = ["qqqqqqqqqqqq"];
    public const CONTROL_STRING_MULTIPLE2 = ["vdfb 546", "выапап 54"];
    public const UF_CRM_DATE = "Дата";
    public const UF_CRM_DATETIME = "ДатаВремя2";
    public const UF_CRM_DATE_MULTIPLE = "ДатаМнож";
    public const UF_CRM_DATETIME_MULTIPLE = "ДатаВремяМнож";
    public const UF_CRM_DATE_REQUIRED = "ДатаОбязат";
    public const CONTROL_DATE = "01.01.2021";
    public const CONTROL_DATETIME = "2020-12-04 02:20:00";
    public const CONTROL_ENUM_MULTIPLE = [69,73];
    public const CONTROL_ENUM = 61;

    protected $b24Id = 1261;
    protected $entityId = Entity::CONTACT_ENTITY_ID;

    //protected $b24Id = 11;
    //protected $entityId = Entity::COMPANY_ENTITY_ID;

    ######################################
    # STRING
    public function test_string_valid(): void
    {

        $this->value_is_valid(
            "тест строка валидная.xlsx",
            static::CONTROL_STRING,
             "Строка"
        );

    }
    public function test_string_multiple_valid(): void
    {
        $this->value_is_valid(
            "тест множественная строка.xlsx",
            static::CONTROL_STRING_MULTIPLE,
            "СтрокаМнож");

    }
    public function test_string_multiple_valid2(): void
    {
        $this->value_is_valid(
            "тест множественная строка2.xlsx",
            static::CONTROL_STRING_MULTIPLE2,
            "СтрокаМнож" );

    }
    public function test_string_multiple_cleared(): void
    {
        $this->value_is_valid(
            "тест множественная строка очистка.xlsx",
            [],
            "СтрокаМнож" );

    }
    public function test_string_cleared_when_empty(): void
    {
        $this->is_cleared_when_empty(
            "очищает необяз строку при пустом значении.xlsx",
            "Строка"
        );

    }
    public function test_error_when_required_string_is_empty(): void
    {
        $this->error_log_contains_errormsg("ошибка при пустой обязат. строке.xlsx");

    }
    public function test_error_when_required_string_multiple_is_empty(): void
    {
        $this->error_log_contains_errormsg("ошибка при пустой обязат. множ. строке.xlsx");
    }


    ######################################
    # INTEGER

    public function test_integer_valid(): void
    {
        $this->value_is_valid(
            "тест число валидное.xlsx",
            static::CONTROL_INTEGER,
            "Число");

    }
    public function test_double_valid(): void
    {
        $this->value_is_valid(
            "тест число десятичн. валидное.xlsx",
            static::CONTROL_DOUBLE,
            "Число");

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
        $this->error_log_contains_errormsg("ошибка невалидное число.xlsx", "не соответствует типу");

    }
    public function test_error_when_int_multiple_not_valid(): void
    {
        $this->error_log_contains_errormsg("ошибка невалидное множ. число.xlsx", "не соответствует типу");

    }

    public function test_error_when_required_int_is_empty(): void
    {
        $this->error_log_contains_errormsg("ошибка при пустой обязат. число.xlsx");

    }
    public function test_error_when_required_int_multiple_is_empty(): void
    {
        $this->error_log_contains_errormsg("ошибка при пустой обязат. множ. число.xlsx");
    }


    ######################################
    # DATE,DATETIME

    public function test_date_valid(): void
    {
        $this->value_is_valid(
            "тест дата.xlsx",
            static::CONTROL_DATE,
            "Дата",
                'date');

    }

    public function test_datetime_valid(): void
    {
        $this->value_is_valid(
            "тест датавремя2.xlsx",
            static::CONTROL_DATETIME,
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
        $this->error_log_contains_errormsg("тест дата невалидная.xlsx", "не соответствует типу");

    }
    public function test_error_when_datetime_is_not_valid(): void
    {
        $this->error_log_contains_errormsg("тест датавремя2 невалидная.xlsx", "не соответствует типу");

    }
    public function test_error_when_date_multiple_is_not_valid(): void
    {
        $this->error_log_contains_errormsg("тест дата множ невалид.xlsx", "не соответствует типу");

    }
    public function test_error_when_one_of_date_multiple_is_not_valid(): void
    {
        $this->error_log_contains_errormsg("тест дата множественное невалидное.xlsx", "не соответствует типу");

    }

    public function test_date_require(): void
    {
        $this->error_log_contains_errormsg("тест дата обязательная с пустым значением.xlsx");

    }
    public function test_date_multiple_require(): void
    {
        $this->error_log_contains_errormsg("тест дата обязательная множ с пустым значением.xlsx");

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
        $this->error_log_contains_errormsg("тест данет невалидный.xlsx", "не соответствует типу");

    }


    ######################################
    # ENUM
    public function test_enum_valid(): void
    {
        $this->value_is_valid(
            "тест список валид.xlsx",
            static::CONTROL_ENUM,
            "Список");

    }

    public function test_enum_multiple_valid(): void
    {
        $this->value_is_valid(
            "тест список множ валид.xlsx",
            static::CONTROL_ENUM_MULTIPLE,
            "СписокМнож");

    }

    public function test_enum_cleared_when_empty(): void
    {
        $this->value_is_valid(
            "тест список пкустой.xlsx",
            "",
            "Список");

    }
    public function test_enum_multiple_cleared_when_empty(): void
    {
        $this->value_is_valid(
            "тест список множественный пустой.xlsx",
            [],
            "СписокМнож");

    }


    public function test_error_when_enum_is_not_valid(): void
    {
        $this->error_log_contains_errormsg("тест список невалидный.xlsx", "недопустимое значение поля");

    }
    public function test_error_when_enum_multiple_is_not_valid(): void
    {
        $this->error_log_contains_errormsg("тест список множ невалид0.xlsx", "недопустимое значение поля");

    }

    public function test_error_when_one_of_enum_multiple_is_not_valid(): void
    {
        $this->error_log_contains_errormsg("тест список множ невалид.xlsx", "является недопустимым");

    }

    public function test_enum_required(): void
    {
        $this->error_log_contains_errormsg("тест список обяз пкустой.xlsx");

    }
    public function test_enum_required_multiple(): void
    {
        $this->error_log_contains_errormsg("тест список множественный обязательный пустой.xlsx");

    }



    ######################################
    # MONEY
    public function test_money_valid(): void
    {
        $this->value_is_valid(
            "тест деньги.xlsx",
            '150|RUB',
            "Деньги");

    }
    public function test_money2_valid(): void
    {

        $this->value_is_valid(
            "тест деньги юсд double.xlsx",
            '97.55|USD',
            "Деньги");

    }
    public function test_money_multiple_valid(): void
    {
        $this->value_is_valid(
            "тест деньги множ.xlsx",
            ['97.55|USD', '150|RUB', '64|RUB'],
            "ДеньгиМнож");

    }
    public function test_money_cleared_when_empty(): void
    {
        $this->value_is_valid(
            "тест деньги пустой.xlsx",
            "",
            "Деньги");

    }
    public function test_money_multiple_cleared_when_empty(): void
    {
        $this->value_is_valid(
            "тест деньги множ пустой.xlsx",
            [],
            "ДеньгиМнож");

    }
    public function test_error_when_money_is_not_valid(): void
    {
        $this->error_log_contains_errormsg("тест деньги невалидное.xlsx", "не соответствует типу");

    }
    public function test_error_when_money_multiple_is_not_valid(): void
    {
        $this->error_log_contains_errormsg("тест деньги множ невалидное.xlsx", "не соответствует типу");

    }
    public function test_error_when_one_of_money_multiple_is_not_valid(): void
    {
        $this->error_log_contains_errormsg("тест деньги множ невалидное1.xlsx", "не соответствует типу");

    }


    public function test_money_required(): void
    {
        $this->error_log_contains_errormsg("тест деньги обяз.xlsx");

    }
    public function test_money_required_multiple(): void
    {
        $this->error_log_contains_errormsg("тест деньги множ обяз.xlsx");

    }




}
