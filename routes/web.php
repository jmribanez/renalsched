<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TechnicianController;
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

Route::get('/', [HomeController::class,'index']);
Route::resource('technicians', TechnicianController::class);
Route::get('/schedules/{year?}/{month?}',[ScheduleController::class, 'index']);
Route::post('/schedules/generate', [ScheduleController::class,'generate'])->name('schedules.generate');
