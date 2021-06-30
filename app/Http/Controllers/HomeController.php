<?php

namespace App\Http\Controllers;

use App\Models\ProcessHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        $lastProcess = ProcessHistory::orderBy('process_end','desc')->first();

        return view('home',compact('lastProcess'));
    }

    public function processHandler(Request $request){
        $rules = [
            'entity_id'=>'required',
            'file'=>'required|mimes:csv,txt',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()){
            return response()->json(['errors'=>$validator->errors()]);
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
