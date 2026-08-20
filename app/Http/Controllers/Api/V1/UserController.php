<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDetailsRequest;
use App\Http\Resources\Admin\UserResource;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if(auth()->user()->role == 'user'){
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        try {
            $query = User::query();

            $query->when($request->filled('search'), function ($q) use ($request){
                $q->where(function ($subQuery) use ($request){
                    $subQuery->where('name', 'like','%'. $request->search . '%')
                            ->orWhere('email', 'like','%'. $request->search . '%');
                });
            });

            $query->orderBy('created_at', 'desc');

            $users = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar user berhasil ditampilkan.',
                'data' =>  UserResource::collection($users)
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'data' => null,
            ], 500);
        }
    }
    
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
