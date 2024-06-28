<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Handle user login and return JWT and refresh token.
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $credentials = $request->only('email', 'password');

            if (!$token = auth()->guard('api')->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau Password Anda salah'
                ], 401);
            }

            $user = auth()->guard('api')->user();

            $responseData = [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'nama' => $user->nama,
            ];

            return response()->json([
                'success' => true,
                'user'    => $responseData,
                'token'   => $token
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'nama' => 'required|max:255',
                'username' => 'required|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
            ]);

            $user = User::create([
                'username' => $data['username'],
                'nama' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            return response()->json(['message' => 'Registration successful.'], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Refresh a JWT token.
     */
    public function refresh(Request $request)
    {
        try {
            $refreshToken = $request->bearerToken();
            Log::info('Received refresh token: ' . $refreshToken);

            if (!$refreshToken) {
                return response()->json(['error' => 'No token provided'], 401);
            }

            JWTAuth::setToken($refreshToken); // Set the token explicitly
            $newToken = JWTAuth::refresh();   // Now attempt to refresh it
            Log::info('New refresh token: ' . $newToken);

            return response()->json([
                'access_token' => $newToken,
                'token_type' => 'bearer'
            ], 200);
        } catch (TokenExpiredException $e) {
            Log::error('Refresh token expired: ' . $e->getMessage());
            return response()->json(['error' => 'Refresh token expired', 'message' => $e->getMessage()], 401);
        } catch (TokenInvalidException $e) {
            Log::error('Invalid refresh token: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid refresh token', 'message' => $e->getMessage()], 401);
        } catch (JWTException $e) {
            Log::error('Token refresh failed: ' . $e->getMessage());
            return response()->json(['error' => 'Token refresh failed', 'message' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            Log::error('Server error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle user logout and invalidate the JWT.
     */
    public function logout(Request $request)
    {
        try {
            $token = JWTAuth::getToken();
            JWTAuth::invalidate($token);

            return response()->json(['message' => 'Logout successful.'], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Logout failed', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }
}
