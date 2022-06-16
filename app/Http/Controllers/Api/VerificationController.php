<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Aloha\Twilio\Twilio;
use App\Events\UserRegistered;
use App\Models\User;

class VerificationController extends Controller
{
    public function getCode(Request $request)
    {
        $request->validate([
            'phone' => 'required'
        ]);

        $phone = $request->phone;
        $code = random_int(100000, 999999);
        $message = 'Your verification code is: ' . $code;
        try {

            $user = User::create([
                'name' => 'user',
                'email' => 'temp_user@email.com',
                'password' => bcrypt('1234asd'),
                'phone' => $phone,
                'verification_code' => $code,
                'is_phone_verified' => false
            ]);

            UserRegistered::dispatch($phone, $message);

            $token = $user->createToken('mobile_app')->plainTextToken;

            return response()->json([
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => true
            ]);
        }
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);
        $user = $request->user();
        if ($user) {
            if ($user->verification_code == $request->code) {
                $user->is_phone_verified;
                $user->save();

                return response()->json([
                    'message' => 'Phone number is verified',
                    'token' => $request->bearerToken()
                ]);
            }
            return response()->json([
                'message' => 'Verification code is incorrect',
            ]);
        }

        return response()->json([
            'message' => 'Invaild Token',
        ]);
    }

    public function updateUser(Request $request)
    {

        $user = $request->user();

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'unique:users,email,' . $user->id,
            'password' => 'required',
        ]);

        if ($user) {
            $user->name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();

            return response()->json([
                'message' => 'Account details are updated',
                'token' => $request->bearerToken()
            ]);
        }
    }

    public function getUser(Request $request)
    {
        return $request->user();
    }
}
