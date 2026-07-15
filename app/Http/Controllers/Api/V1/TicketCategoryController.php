<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketCategoryStoreRequest;
use App\Http\Resources\TicketCategoryResource;
use App\Models\Event;
use App\Models\TicketCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketCategoryController extends Controller
{
    public function index(Request $request)
    {
        if(auth()->user()->role == 'user'){
            return response()->json([
                'message' => 'Anda tidak memiliki akses.'
            ],403);
        }

        try {
            $query = TicketCategory::query();

            if($request->search){
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $ticketCategories = $query->get();

            return response()->json([
                'message' => 'Event berhasil ditampilkan',
                'data' => TicketCategoryResource::collection($ticketCategories)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'data' => null
            ], 500);
        }
    }
    
    public function show(Request $request, $event_id)
    {
        if(auth()->user()->role == 'user'){
            return response()->json([
                'message' => 'Anda tidak memiliki akses.'
            ],403);
        }

        try {
            $ticketCategory = TicketCategory::where('event_id', $event_id)->get();
    
            if(!$ticketCategory){
                return response()->json([
                    'message' => 'Ticket category tidak ditemukan.',
                    'data' => null
                ],404);
            }
    
            return response()->json([
                'message' => 'Event berhasil ditampilkan',
                'data' => $ticketCategory
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'data' => null
            ], 500);
        }
    }

    public function showDetail(Request $request, $event_id, $ticket_id)
    {
        if(auth()->user()->role == 'user'){
            return response()->json([
                'message' => 'Anda tidak memiliki akses.'
            ],403);
        }

        try {
            $ticketCategory = TicketCategory::where('id', $ticket_id)->where('event_id', $event_id)->firstOrFail();
    
            if(!$ticketCategory){
                return response()->json([
                    'message' => 'Ticket category tidak ditemukan.',
                    'data' => null
                ],404);
            }
    
            return response()->json([
                'message' => 'Event berhasil ditampilkan',
                'data' => new TicketCategoryResource($ticketCategory)
            ], 200);

        }   catch (ModelNotFoundException $e) {
                return response()->json([
                    'message' => 'Ticket category tidak ditemukan.',
                    'data' => null
                ], 404);
        }   catch (\Exception $e) {
                return response()->json([
                    'message' => 'Terjadi kesalahan',
                    'data' => null
                ], 500);
        }
    }

    public function store(TicketCategoryStoreRequest $request, $event_id)
    {
        if(auth()->user()->role == 'user'){
            return response()->json([
                'message' => 'Anda tidak memiliki akses.'
            ],403);
        }

        $data = $request->validated();

        DB::beginTransaction();

        try {
            $event = Event::where('id', $event_id)->first();

            if(!$event){
                return response()->json([
                    'message' => 'Event tidak ditemukan.',
                    'data' => null
                ],404);
            }

            $ticketCategory = new TicketCategory();
            $ticketCategory->name = $data['name'];
            $ticketCategory->event_id = (int)$event_id;
            $ticketCategory->price = $data['price'];
            $ticketCategory->quota = $data['quota'];
            $ticketCategory->reserved = $data['reserved'];
            $ticketCategory->save();

            DB::commit();

            return response()->json([
                'message' => 'Ticket category berhasil ditambahkan.',
                'data' => new TicketCategoryResource($ticketCategory)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Terjadi Kesalahan',
               'error' => $e->getMessage()
            ], 500);
       }
    }

}
