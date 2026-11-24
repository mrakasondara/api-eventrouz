<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDetailsRequest;
use App\Http\Requests\UpdateHeaderRequest;
use App\Http\Resources\Admin\UserResource;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use App\Services\AppwriteStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    protected AppwriteStorageService $appwriteStorage;

    public function __construct(AppwriteStorageService $appwriteStorage)
    {
        $this->appwriteStorage = $appwriteStorage;
    }
    
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

            $query->where('role', 'user');

            $query->when($request->filled('search'), function ($q) use ($request){
                $q->where(function ($subQuery) use ($request){
                    $subQuery->where('name', 'like','%'. $request->search . '%')
                            ->orWhere('email', 'like','%'. $request->search . '%');
                });
            });

            $query->when($request->filled('limit'), function ($q) use ($request){
                $q->limit((int) $request->limit);
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
            ], 500);
        }
    }

    public function updateHeaderData(UpdateHeaderRequest $request)
    {

        
        try {
            $data = $request->validated();

            $userId = auth()->user()->id;

            $userData = User::findOrFail($userId);

            $imageRequest = $request->file('image_profile');
            $oldImage = $userData['image_profile'];

            $userData['name'] = $data['name'];

            if($imageRequest){
                $response = $this->appwriteStorage->putFile($oldImage, $imageRequest);
                $fileId = $response['$id'] ?? null;

                if(!$fileId){
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mendapatkan File ID dari Appwrite.'
                    ], 500);
                }

                $userData['image_profile'] = $fileId;
            }

            $userData->save();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diubah.',
            ], 200); 
        } catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
        ], 500);
        }
    }
}
