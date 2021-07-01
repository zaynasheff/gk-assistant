<?php

namespace App\Http\Controllers;

use App\Imports\EntityDataHeadingsImport;
use App\Imports\EntityDataImport;
use App\Models\B24FieldsDictionary;
use App\Models\ProcessHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
        $lastProcess = ProcessHistory::orderBy('process_end','desc')->first();

        return view('home',compact('lastProcess','is_running'));
    }

    public function processHandler(Request $request){

        $rules = [
            'entity_id'=>'required',
            //'file'=>'required|mimes:csv,txt',
            'file'=>'required',
        ];
        $request->validate($rules);

        ///////////////Импорт файла///////////

        //заголовки

        $headings = (new HeadingRowImport)->toArray($request->file('file'))[0][0];

        //Изначальная валидация

        //отсутствие ячейки со значением “ID”

        $errors = false;

        if(!in_array('ID',$headings)){
            $errors = true;
            $message = 'Процесс не запущен! Отсутствие ячейки со значением ID';

        }
        //совпадение значений любых двух ячеек
        if (count($headings) !== count(array_unique($headings))){

            $errors = true;
            $message = 'Процесс не запущен! Совпадение значений двух ячеек';

        }

        //отсутствие в выбранной сущности Битрикс полей с названием, равным значению ячейки
        $b24fields = B24FieldsDictionary::where('entity_id',$request->entity_id)->pluck('title')->toArray();

        $diffFields =  array_diff($headings, $b24fields);
        if(count($diffFields)>0){
            $errors = true;
            $message = 'Процесс не запущен! Отсутствие в выбранной сущности Битрикс полей: ' .implode(', ',$diffFields);
        }

        //наличие в сущности битрикс более одного поля с с названием, равным значению ячейки
        if (count($b24fields) !== count(array_unique($b24fields))){
            $errors = true;
            $message = 'Процесс не запущен! Наличие в сущности битрикс более одного поля с с названием';
        }


        if ($errors === true){
            return redirect()->back()->with('error',$message);
        }

        dd($headings[0][0]);

        $entityData = Excel::import(new EntityDataImport, $request->file('file'));

        dd($entityData);


       //Проверка на уже запущенный процесс
        if(ProcessHistory::isRunning()){
            dd('yes');
        }
        else{
            dd('no');
        }


        //Обработка файла

        //запись в таблицу ProcessHistory

        $lines_count = rand(1000,1500); //demo
        $lines_error = 2;//demo
        $lines_success = (int)($lines_count*0.99);//demo

        $process = ProcessHistory::create([
            'uid'=>Str::random(15),
            'process_start'=>now()->toDateTimeString(),
            'process_end'=>now()->addMinutes(25)->toDateTimeString(), //demo
            'entity_id'=>$request->entity_id,
            'lines_count'=>$lines_count, //demo
            'lines_success'=>$lines_success, //demo
            'lines_error'=>$lines_error, //demo
        ]);

        $process_data = [
            'uid'=>$process->uid,
            'process_start'=>Carbon::parse($process->process_start)->format('d.m.Y H:i:s'),
            'process_end'=>Carbon::parse($process->process_end)->format('d.m.Y H:i:s'), //demo
            'entity_title'=>$process->entity->title,
            'lines_count'=>$process->lines_count, //demo
            'lines_success'=>(int)($lines_count*0.2), //demo
            'lines_error'=>$process->lines_error, //demo
        ];

        return response()->json(['success'=>['message'=>'Процесс обработки запущен','process_data'=>$process_data]]);

    }

    public function processTerminate(Request $request){
        $process = ProcessHistory::where('uid',$request->uid)->first();
        $process->lines_success = $request->lines_success;
        $process->save();
        return response()->json(['success'=>'Процесс успешно остановлен']);
    }
}
