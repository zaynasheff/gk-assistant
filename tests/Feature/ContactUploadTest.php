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

class ContactUploadTest extends TestCase
{

    protected $b24Id = 1261;
    protected $entityId = Entity::CONTACT_ENTITY_ID;

    public function test_contact_string_valid(): void
    {
        $this->value_is_valid(
            "контакт тест строка валидная.xlsx",
            self::CONTROL_STRING,
             self::UF_CRM_STRING
        );

    }

    public function test_contact_string_multiple_valid(): void
    {
        $this->value_is_valid(
            "тест множественная строка.xlsx",
            static::CONTROL_STRING_MULTIPLE,
            self::UF_CRM_STRING_MULTIPLE );

    }
    public function test_contact_string_multiple_valid2(): void
    {
        $this->value_is_valid(
            "тест множественная строка2.xlsx",
            static::CONTROL_STRING_MULTIPLE2,
            self::UF_CRM_STRING_MULTIPLE );

    }

    public function test_contact_string_multiple_cleared(): void
    {
        $this->value_is_valid(
            "тест множественная строка очистка.xlsx",
            [],
            self::UF_CRM_STRING_MULTIPLE );

    }

    public function test_contact_string_cleared_when_empty(): void
    {
        $this->string_is_cleared_when_empty(
            "контакт очищает необяз строку при пустом значении.xlsx",
            self::UF_CRM_STRING
        );

    }

    public function test_contact_error_when_requred_is_empty(): void
    {
        $this->error_when_requred_is_empty("контакт ошибка при пустом оьязат. поле.xlsx");

    }

    public function test_contact_integer_valid(): void
    {
        $this->value_is_valid(
            "контакт тест число валидное.xlsx",
            self::CONTROL_INTEGER,
            self::UF_CRM_INTEGER);

    }
    public function test_contact_double_valid(): void
    {
        $this->value_is_valid(
            "контакт тест число десятичн. валидное.xlsx",
            self::CONTROL_DOUBLE,
            self::UF_CRM_INTEGER);

    }




}
