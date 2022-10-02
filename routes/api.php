<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

# File Upload API
Route::apiResource('file', FileUploadController::class)->middleware('auth:api');
Route::apiResource('user.file', FileUploadController::class)->middleware('auth:api');;
Route::get('user/{user}/file/{file}', [FileUploadController::class, 'show'])->scopeBindings()->middleware('auth:api');;
Route::post('user/{user}/file/{file}', [FileUploadController::class, 'update'])->scopeBindings()->middleware('auth:api');;
Route::delete('user/{user}/file/{file}', [FileUploadController::class, 'destroy'])->scopeBindings()->middleware('auth:api');;

# User Register API
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::get('fail', [UserController::class, 'unaccess'])->name('login');






