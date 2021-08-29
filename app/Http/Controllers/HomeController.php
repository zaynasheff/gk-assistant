<?php

namespace App\Http\Controllers;


use App\Helpers\ExcelHelper;
use App\Imports\EntityDataImport;
use App\Imports\FieldsMapper;
use App\Models\B24FieldsDictionary;
use App\Models\Entity;
use App\Models\ProcessHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
        //$this->middleware('auth');
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
            'file'=>'required|mimes:csv,txt,xls,xlsx',
            //'file' => 'required',
        ];
        //$request->validate($rules);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

           $requestErrors = $validator->getMessageBag()->getMessages();


            //return view('home', compact('lastProcess', 'is_running','requestErrors'));

            return redirect()->route('home',[
                'requestErrors'=>$requestErrors
            ]);
        }

        if ($request->file('file')->getClientOriginalExtension() ==='txt'){
            $requestErrors = ['file'=>[0=>'Неподдерживаемый формат файла']];
            //return view('home', compact('lastProcess', 'is_running','requestErrors'));

            return redirect()->route('home',[
                'requestErrors'=>$requestErrors
            ]);
        }

        try {

            //сущность для сообщений
            $entity = Entity::findOrFail($request->entity_id);

            //заголовки
            config(['excel.imports.csv.input_encoding' =>
                    \PhpOffice\PhpSpreadsheet\Reader\Csv::guessEncoding($request->file('file'), 'Windows-1251')
                ]
            );

            $headings = (new HeadingRowImport)->toArray($request->file('file'))[0][0];

            FieldsMapper::map($headings);
        }
        catch (\Exception $e){
            $message = $e->getMessage();
            return redirect()->route('home',[
                'message'=>$message
            ]);
        }


        //Изначальная валидация

        //отсутствие ячейки со значением “ID”

        $errors = false;

        if (!in_array('ID', $headings)) {
            $errors = true;
            $message = 'Процесс не запущен! В заголовке отсутствует столбец со значением ID';

        }
        //совпадение значений любых двух столбцов
        if ( $doubled = collect($headings)->countBy()->search(function ($item) {
            return $item > 1 ;
        }))
        {

            $errors = true;
            $message = 'Процесс не запущен! Совпадение значений двух названий столбцов: ' . $doubled;

        }



        //отсутствие в выбранной сущности Битрикс полей с названием, равным значению ячейки
        $b24fields = B24FieldsDictionary::where('entity_id', $request->entity_id)->pluck('title')->toArray();
        $b24fields_col = collect($b24fields);

        $diffFields = array_diff($headings, $b24fields);
        // регистронезависимый вариант : array_udiff($headings, $b24fields, 'strcasecmp');

        if (count($diffFields) > 0) {
            $errors = true;
            $message = 'Процесс не запущен! В выбранной вами сущности '.$entity->title .
                ' нет указанных в файле ' .
                $request->file('file')->getClientOriginalName().
                ' полей: '.implode(', ', $diffFields)
            . '(если вы создали это поле недавно, подождите несколько минут перед запуском этого процесса)'
            . '. Обратите внимание: названия полей в файле должны быть в том же регистре, что и в CRM';
        }

        //пустое значение ячейки, если хотя бы в одной ячейке в любой строке данного столбца есть непустое значение
        if (in_array(null, $headings)) {

            $keyHeading = array_search(null, $headings) + 1;
            $errors = true;
            $message = 'Процесс не запущен! Пустое значение в заголовке,&nbsp;столбец&nbsp;&nbsp;'.  $keyHeading;

        }
        //наличие в сущности битрикс более одного поля с с названием, равным значению ячейки
        if ( $doubled = $b24fields_col->countBy()->search(function ($item, $key) use ($headings) {
            return $item > 1 && in_array($key, $headings);
        })) {
            $errors = true;
            $message = 'Процесс не запущен! Наличие в сущности битрикс '.$entity->title.'  более одного поля с одним названием: ' . $doubled;
        }


        /////временно отключаем валидацию
        //$errors = false;

        if ($errors === true) {

            return redirect()->route('home',[
                'message'=>$message
            ]);
        }

        ///////////////Импорт файла///////////
        try{
            Excel::import(new EntityDataImport, $request->file('file'));
        }

        catch (\Exception $e){
            $message = $e->getMessage();
            return redirect()->route('home',[
                'message'=>$message
            ]);
        }



        //return redirect()->back()->with('success', 'Процесс обработки запущен');
        $success = 'Процесс обработки запущен';
        return redirect()->route('home',[
              'success'=>$success
        ]);

    }

    public function getLog()
    {

        echo Storage::disk('log')->get('update.log');


        printf(
            "<br><a href='%s'>Скачать</a>",  action([self::class, '__getLog'])
        );


    }

    public function __getLog()
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


            $success = 'Процесс был прерван успешно';

            return redirect()->route('home',[
                'success'=>$success
            ]);
        } catch (\Exception $e) {
            Log::channel('log')->error('Ошибка прерывания процесса UID: ' . $e->getMessage());
            $message = $e->getMessage();

            return redirect()->route('home',[
                'message'=>$message
            ]);
        }


    }

    public function getSuccessCount(){
        $process = ProcessHistory::where('processing',1)->first();
        if ($process){
            $count = $process->lines_count;
            $countSuccess = $process->lines_success;
            $countError = $process->lines_error;
            $timeToFinish = Carbon::parse($process->process_end)->diff(now())->format('%H:%I:%S');
            return response()->json([
                'count'=>$count,
                'countSuccess'=>$countSuccess,
                'countError'=>$countError,
                'processing'=>1,
                'timeToFinish'=>$timeToFinish
            ]);
        }

        return response()->json([
                'processing'=>3
            ]);



    }

}
