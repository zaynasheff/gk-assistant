<?php

namespace App\Imports;

use App\Jobs\ProcessUpdateEntityJob;
use App\Models\ProcessHistory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

HeadingRowFormatter::default('none');

class EntityDataImport implements ToCollection,WithHeadingRow
{


    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {

         ProcessHistory::create([
            'uid'=>Str::random(15),
            'process_start'=>now()->toDateTimeString(),
            'process_end'=>null,
            'entity_id'=>request()->entity_id,
            'lines_count'=>count($collection),
            'lines_success'=>0,
            'lines_error'=>0,
            'processing'=>1 //процесс запущен
        ]);


        $entityData =  $collection->toArray();

        //чистим лог
        Artisan::call('log:clear');

        //Отправка в очередь
        foreach ($entityData as $index=>$line){
            $lineNum = (int) $index+1; //номер строки
            ProcessUpdateEntityJob::dispatch($lineNum,request()->entity_id, $line);
        }
    }




}
