<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::get('/index',[UserController::class,'index']);
Route::post('/login', [UserController::class,'login']);
Route::post('/new', [UserController::class,'register']);

Route::group(['middleware' => ['jwt']], function () {
    Route::get('/me', [UserController::class, 'show']);
    Route::put('/me', [UserController::class, 'update']);
    Route::delete('/me', [UserController::class, 'delete']);
});
