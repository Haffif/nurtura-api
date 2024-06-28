<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public static function reset_password(Request $request)
    {
        try {
            $data = $request->validate([
                'otp_code' => 'required|min:4',
            ]);

            $user = User::where('otp_code', $data['otp_code'])
                ->orderBy('updated_at', 'desc')  // Assuming 'created_at' is available and correctly indexed
                ->first();
                
            if ($user) {
                $now = Carbon::now();
                if($user->otp_expiry > $now){
                    $data_user = [
                        'id' => $user->id,
                        'nama' => $user->nama,
                        'email' => $user->email,
                        'username' => $user->username,
                    ];
                    return response()->json([
                        'success' => true,
                        'data' => $data_user
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode otp expired'
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode otp salah'
                ], 400);
            }
           
        } catch (ValidationException $e) {
            return response()->json(['error' => false, 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }
    public static function update_password (Request $request){
        $id_user = $request->query('id_user');

        try {
            $data = $request->validate([
                'password' => 'required|min:8|confirmed',
            ]);
            $user = User::find($id_user);
            if ($user) {
                $user->password = Hash::make($data['password']);
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Password berhasil diupdate'
                ], 200);
            }        
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }    
}
