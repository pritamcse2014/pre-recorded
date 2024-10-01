<?php

namespace App\Http\Controllers;

use App\Helper\JWTToken;
use App\Mail\OTPMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class UserController extends Controller
{
    function LoginPage():View{
        return view('pages.auth.login-page');
    }

    function RegistrationPage():View{
        return view('pages.auth.registration-page');
    }

    function SendOTPPage():View{
        return view('pages.auth.send-otp-page');
    }

    function VerifyOTPPage():View{
        return view('pages.auth.verify-otp-page');
    }

    function ResetPasswordPage():View{
        return view('pages.auth.reset-password-page');
    }

    function ProfilePage():View{
        return view('pages.dashboard.profile-page');
    }

    function UserRegistration(Request $request) {
        try {
            User::create([
                'firstName' => $request->input('firstName'),
                'lastName' => $request->input('lastName'),
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
                'password' => $request->input('password'),
            ]);
            return response()->json([
                'status' => 'Success',
                'message' => 'User Registration Successfully.'
            ], 200);
        }
        catch (Exception $e) {
            return response()->json([
                'status' => 'Failed',
//                'message' => $e->getMessage()
                'message' => 'User Registration Failed.'
            ], 200);
        }
    }

    function UserLogin(Request $request) {
        $count = User::where('email', '=', $request->input('email'))
            ->where('password', '=', $request->input('password'))
            ->select('id')->first();
        if ($count !== null)
        {
//            User Login-> JWT Token Issue
            $token = JWTToken::CreateToken($request->input('email'), $count->id);
            return response()->json([
                'status' => 'Success',
                'message' => 'User Login Successfully.',
                // 'token' => $token
            ], 200)->cookie('token', $token, 60*24*30);
        }
        else
        {
            return response()->json([
                'status' => 'Failed',
//                'message' => $e->getMessage()
                'message' => 'Unauthorized.'
            ], 200);
        }
    }

    function SendOTPCode(Request $request) {
        $email = $request->input('email');
        $otp = rand(1000, 9999);
        $count = User::where('email', '=', $email)->count();
        if ($count == 1) {
//            OTP Send
            Mail::to($email)->send(new OTPMail($otp));
//            OTP Code Insert DB Table Update
            $count = User::where('email', '=', $email)->update(['otp'=>$otp]);
            return response()->json([
                'status' => 'Success',
                'message' => 'OTP Code Send Your Email Successfully.',
            ], 200);

        }
        else {
            return response()->json([
                'status' => 'Failed',
//                'message' => $e->getMessage()
                'message' => 'Unauthorized.'
            ], 200);
        }
    }

    function VerifyOTP(Request $request) {
        $email = $request->input('email');
        $otp = $request->input('otp');
        $count = User::where('email', '=', $email)
            ->where('otp', '=', $otp)->count();
        if ($count == 1) {
//            OTP code Update DB Table
            User::where('email', '=', $email)->update(['otp'=>'0']);
//            Password reset Token Issue
            $token = JWTToken::CreateTokenForSetPassword($request->input('email'));
            return response()->json([
                'status' => 'Success',
                'message' => 'OTP Verification Successfully.',
                'token' => $token
            ], 200);

        }
        else {
            return response()->json([
                'status' => 'Failed',
//                'message' => $e->getMessage()
                'message' => 'Unauthorized.'
            ], 200);
        }
    }

    function ResetPassword(Request $request) {
        try {
            $email = $request->header('email');
            $password = $request->input('password');
            User::where('email', '=', $email)->update(['password'=>$password]);
            return response()->json([
                'status' => 'Success',
                'message' => 'Password Reset Successfully.',
            ], 200);
        }
        catch (Exception $e) {
            return response()->json([
                'status' => 'Failed',
//                'message' => $e->getMessage()
                'message' => 'Password Reset Failed.'
            ], 200);
        }
    }

    function UserLogout(Request $request) {
        return redirect('/userLogin')->cookie('token', '', -1);
    }
}
