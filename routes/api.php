<?php
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;

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
 
Route::group([
    'middleware' => 'api',
], function () {
    //User API's
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->name('me');
    Route::get('/verify/{code}', [AuthController::class, 'verify'])->name('verify');


    //Todo API's
    Route::get('/todos', [TodoController::class, 'index']);
    Route::post('/createTodo', [TodoController::class, 'store']);
    Route::get('/getTodo/{id}', [TodoController::class, 'show']);
    Route::put('/updateTodo/{id}', [TodoController::class, 'update']);
    Route::delete('/deleteTodo/{id}', [TodoController::class, 'destroy']);

});