<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\api\AuthController;
use App\Http\Controllers\Api\VerificationController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return $user->createToken($request->device_name)->plainTextToken;
});

Route::post('get_code', [VerificationController::class, 'getCode']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('verify_code', [VerificationController::class, 'verifyCode']);
    Route::post('update_user', [VerificationController::class, 'updateUser']);
    Route::get('user', [VerificationController::class, 'getUser']);
});
