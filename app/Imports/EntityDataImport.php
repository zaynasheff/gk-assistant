<?php

namespace App\Imports;

use Illuminate\Support\Collection;
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
        dd($collection->first());
    }


}
