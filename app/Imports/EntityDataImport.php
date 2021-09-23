<?php

namespace App\Imports;

use App\Jobs\ProcessUpdateEntityJob;
use App\Models\ProcessHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

//HeadingRowFormatter::default('none');
HeadingRowFormatter::default('format_b24_fields');


class EntityDataImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithCalculatedFormulas
{


    /**
     * @param Collection $collection
     * @return \Illuminate\Http\RedirectResponse
     */
    public function collection(Collection $collection)
    {

        if (ProcessHistory::isRunning()) {
            return redirect()->route('home')->with('error', 'Процесс уже запущен!');
        }

        $process = ProcessHistory::create([
            'uid' => Str::random(15),
            'process_start' => now()->toDateTimeString(),
            'process_end' => now()->addSeconds(count($collection))->toDateTimeString(),
            'entity_id' => request()->entity_id,
            'lines_count' => count($collection),
            'lines_success' => 0,
            'lines_error' => 0,
            'processing' => 1 //процесс запущен
        ]);


        $entityData = $collection->toArray();

        //рестарт очередей , важно т.к. AppServiceProvider boot работает только в этом случае
        Artisan::call('queue:restart');
        //чистим лог
        Artisan::call('log:clear');
        //создаем новый лог
        Log::channel('log')->info('Новый процесс запущен. UID: ' . $process->uid);

        //Отправка в очередь
        $time_start = now();

        foreach ($entityData as $index => $line) {
            $lineNum = (int)$index + 1; //номер строки
            if ( ($b24ID = request()->b24ID ) && app()->runningUnitTests() ) {
                $line["ID"] = $b24ID; // для тестов - задать ID через request
            }
                ProcessUpdateEntityJob::dispatch($lineNum, request()->entity_id, $line, $process)->onQueue('EntityDataImport')->delay($time_start->addMicroseconds(1000000));
        }
    }


}
