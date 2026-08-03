<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDetailsRequest;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function updateDetails(UpdateDetailsRequest $request)
    {
        try{
            $user = auth()->user();

            $data = $request->validated();

            DB::transaction(function () use ($user, $data) {
                $user->update([
                    'address' => $data['address'],
                    'gender' => $data['gender'],
                    'phone_number' => $data['phone_number'],
                ]);
            });

            return response()->json([
                    'success' => true,
                    'message' => 'Informasi personal berhasil diubah.',
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
