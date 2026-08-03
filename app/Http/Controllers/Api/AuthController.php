<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        Log::info("Login Request:" . json_encode($request->all()));
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken,
                'user' => $user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;
        $pin = strval(rand(100000, 999999));

        // Delete existing resets
        \DB::table('password_resets')->where('email', $email)->delete();

        // Insert new reset
        \DB::table('password_resets')->insert([
            'email' => $email,
            'token' => bcrypt($pin),
            'created_at' => now(),
        ]);

        // Send email
        try {
            \Mail::raw("Your password reset verification code is: {$pin}", function ($message) use ($email) {
                $message->to($email)
                    ->subject('Password Reset Code');
            });
        } catch (\Exception $e) {
            \Log::error("Failed to send password reset email: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to send reset code email. Please check mail settings.'
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'A verification code has been sent to your email.'
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $reset = \DB::table('password_resets')->where('email', $request->email)->first();

        if (!$reset) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired verification code.'
            ], 400);
        }

        // Check if code is expired (e.g. 60 mins)
        if (now()->subMinutes(60)->gt($reset->created_at)) {
            \DB::table('password_resets')->where('email', $request->email)->delete();
            return response()->json([
                'status' => false,
                'message' => 'Verification code has expired.'
            ], 400);
        }

        // Verify PIN
        if (!\Hash::check($request->token, $reset->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid verification code.'
            ], 400);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => bcrypt($request->password)
        ]);

        // Delete reset record
        \DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Your password has been successfully reset.'
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        if (!\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'The current password you entered is incorrect.'
            ], 400);
        }

        $user->update([
            'password' => bcrypt($request->new_password)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully.'
        ], 200);
    }
}
