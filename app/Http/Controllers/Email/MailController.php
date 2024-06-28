<?php

namespace App\Http\Controllers\Email;

use App\Http\Controllers\Controller;
use App\Mail\ForgetPasswordOTP;
use App\Models\User;  // Pastikan mengimport model User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon; // Import Carbon for date/time handling

class MailController extends Controller
{
    public function send_otp_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'messages' => $validator->errors()], 422);
        }

        $toEmail = $validator->validated()['email'];

        // Cek apakah email ada di database
        $user = User::where('email', $toEmail)->first();

        if (!$user) {
            // Jika tidak ada, kembalikan error response
            return response()->json(['success' => false, 'message' => 'No user found with that email address.'], 404);
        }

        try {
            // Generate an OTP code
            $otpCode = rand(1000, 9999);
            $expiryTime = Carbon::now()->addMinutes(1); 

            // Store OTP in the database or another secure place
            $user->otp_code = $otpCode;
            $user->otp_expiry = $expiryTime;
            $user->save();

            // Detail email with OTP included
            $details = [
                'title' => 'Reset Password Verification',
                'body' => 'Your OTP is: ' . $otpCode . '. Please do not share this code with anyone. This code will expire in 10 minutes.'
            ];

            // Mengirim email
            Mail::to($toEmail)->send(new ForgetPasswordOTP($details));

            // Mengembalikan respons sukses setelah email terkirim
            return response()->json(['success' => true, 'message' => 'Email with OTP has been sent successfully.'], 200);
        } catch (\Exception $e) {
            // Mengembalikan error server
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
