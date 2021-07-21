<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if(auth()->guest()){return redirect()->route('login');}
    return redirect()->route('home');
});

//Auth::routes();
//
//Route::get('/register',function (){
//    return abort(404);
//});
//Route::post('/register',function (){
//    return abort(404);
//});

Route::get('/', function () {

    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::post('/process_handler', [App\Http\Controllers\HomeController::class, 'processHandler'])->name('processHandler');
Route::get('/get_log', [App\Http\Controllers\HomeController::class, 'getLog'])->name('getLog');
Route::post('/process_terminate', [App\Http\Controllers\HomeController::class, 'processTerminate'])->name('processTerminate');
Route::post('/get_success_count', [App\Http\Controllers\HomeController::class, 'getSuccessCount'])->name('getSuccessCount');
