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



}
