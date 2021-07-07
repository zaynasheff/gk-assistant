<?php

namespace App\Http\Controllers;


use App\Imports\EntityDataImport;
use App\Models\B24FieldsDictionary;
use App\Models\ProcessHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;


HeadingRowFormatter::default('none');

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $is_running = ProcessHistory::isRunning();
        $lastProcess = ProcessHistory::orderBy('process_start', 'desc')->first();

        return view('home', compact('lastProcess', 'is_running'));
    }

    public function processHandler(Request $request)
    {

        $rules = [
            'entity_id' => 'required',
            //'file'=>'required|mimes:csv,txt',
            'file' => 'required',
        ];
        $request->validate($rules);


        //заголовки

        $headings = (new HeadingRowImport)->toArray($request->file('file'))[0][0];

        //Изначальная валидация

        //отсутствие ячейки со значением “ID”

        $errors = false;

        if (!in_array('ID', $headings)) {
            $errors = true;
            $message = 'Процесс не запущен! Отсутствие ячейки со значением ID';

        }
        //совпадение значений любых двух ячеек
        if (count($headings) !== count(array_unique($headings))) {

            $errors = true;
            $message = 'Процесс не запущен! Совпадение значений двух названий столбоцов';

        }

        //отсутствие в выбранной сущности Битрикс полей с названием, равным значению ячейки
        $b24fields = B24FieldsDictionary::where('entity_id', $request->entity_id)->pluck('title')->toArray();
        $b24fields_col = collect($b24fields);

        $diffFields = array_diff($headings, $b24fields);

        if (count($diffFields) > 0) {
            $errors = true;
            $message = 'Процесс не запущен! Отсутствие в выбранной сущности Битрикс полей: ' . implode(', ', $diffFields);
        }

        //пустое значение ячейки, если хотя бы в одной ячейке в любой строке данного столбца есть непустое значение
        if (in_array(null, $headings)) {
            $errors = true;
            $message = 'Процесс не запущен! Пустое значение в заголовке';

        }
        //наличие в сущности битрикс более одного поля с с названием, равным значению ячейки
        if ( $doubled = $b24fields_col->countBy()->search(function ($item, $key) {
            return $item > 1;
        })) {
            $errors = true;
            $message = 'Процесс не запущен! Наличие в сущности битрикс более одного поля с одним названием:' . $doubled;
        }


        /////временно отключаем валидацию
       // $errors = false;

        if ($errors === true) {
            return redirect()->back()->with('error', $message);
        }

        ///////////////Импорт файла///////////

        Excel::import(new EntityDataImport, $request->file('file'));


        return redirect()->back()->with('success', 'Процесс обработки запущен');

    }

    public function getLog()
    {

        return Storage::disk('log')->download('update.log');

    }

    public function processTerminate()
    {
        try {
            //чистим очередь
            Artisan::call('queue:clear --queue=EntityDataImport');
            $process = ProcessHistory::where('processing', 1)->first();
            $process->processing = 2;//процесс завершен
            $process->save();
            Log::channel('log')->info('Процесс UID: ' . $process->uid . ' был прерван  ' . now()->format('d.m.Y h:i:s'));

            return redirect()->back()->with('success', 'Процесс был прерван успешно');
        } catch (\Exception $e) {
            Log::channel('log')->error('Ошибка прерывания процесса UID: ' . $e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }


    }

}
