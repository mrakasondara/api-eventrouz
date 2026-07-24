<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show()
    {
        try{
            $user = auth()->user();

            return response()->json([
                'success' => true,
                'message' => 'Profil user berhasil ditampilkan.',
                'data' => new UserProfileResource($user)
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'data' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
