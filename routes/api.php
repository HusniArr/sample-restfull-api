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
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::get('/messages', function() {
    $data = [
        "status" => true,
        "message" => "welcome to message"
    ];
    $headers = [
        "Content-Type" => "application/json"
    ];
    return response()->json($data, 200, $headers);
});

Route::post('/users/register',[UserController::class, 'register']);
Route::post('/users/register/admin',[UserController::class, 'register_admin']);
Route::post('/users/login', [UserController::class, 'login']);
