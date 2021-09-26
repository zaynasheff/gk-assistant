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

class LeadsUploadTest extends ContactUploadTest
{


    public const CONTROL_ENUM = 107;
    public const CONTROL_ENUM_MULTIPLE = ["113","117"];

    protected $b24Id = 1;
    protected $entityId = Entity::LEAD_ENTITY_ID;


}
