<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventStoreRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Event::query();

            $query->orderBy('created_at','desc');
            
            if($request->search){
                $query->where('title','like', '%' . $request->search . '%')
                    ->orWhere('description','like', '%' . $request->search . '%');
            }

            if($request->status){
                $query->where('status', $request->status);
            }

            $events = $query->get();

            return response()->json([
                'message' => 'Event berhasil ditampilkan',
                'data' => EventResource::collection($events)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'data' => null
            ], 500);
        }
    }

    public function store(EventStoreRequest $request)
    {
        if(auth()->user()->role == 'user'){
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }
        
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $event = new Event();
            $event->title = $data['title'];
            $event->description = $data['description'];
            $event->image_thumb = $data['image_thumb'];
            $event->start_at = $data['start_at'];
            $event->end_at = $data['end_at'];
            $event->location = $data['location'];
            $event->status = $data['status'];

            $event->save();

            DB::commit();

            return response()->json([
                'message' => 'Event berhasil ditambahkan',
                'data' => new EventResource($event)
            ], 201);

        } catch (\Exception $e) {
             DB::rollBack();
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $event = Event::where('id', $id)->first();

            if(!$event){
                return response()->json([
                    'message' => 'Event tidak ditemukan',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'message' => 'Event berhasil ditampilkan',
                'data' => new EventResource($event)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'data' => null
            ], 500);
        }
    }
}
