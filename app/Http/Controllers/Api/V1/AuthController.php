<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterStoreRequest;
use App\Http\Requests\LoginStoreRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterStoreRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->save();
            DB::commit();

            return response()->json([
                'message' => 'Register berhasil!',
                'success' => true,
                'data' => [
                    'data' => new UserResource($user)
                    ]
                ], 201);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(LoginStoreRequest $request)
    {
        $credentials = $request->validated();

        try {
            if(!Auth::attempt($credentials)){
                return response()->json([
                    'message' => 'Unauthorized!',
                    'success' => false,
                    'data' => $credentials
                ], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil!',
                'success' => true,
                'data' => [
                    'token' => $token,
                    'user' => new UserResource($user)
                ]
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'success' => false,
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        try {
            $user = Auth::user();

            if($user){
                $user->currentAccessToken()->delete();
    
                return response()->json([
                    'message' => 'Logout berhasil',
                    'success' => true,
                    'data' => null
                ], 200);
            }

            return response()->json([
                'message' => 'Kamu memang belum login atau token sudah tidak valid.',
                'success' => false
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
