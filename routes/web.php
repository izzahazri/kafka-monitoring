<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $tables = DB::select('SELECT * FROM monitors c LEFT JOIN daily_log dl ON c.id=dl.monitor_id AND insert_date=DATE(NOW())');
    return view('overview',get_defined_vars());
})->name('overview');

Route::get('/log', function () {
    $logs = DB::select('SELECT * FROM logs');
    return view('log',get_defined_vars());
})->name('log');


Route::get('/topic', function () {
    return view('tables');
})->name('topic');
