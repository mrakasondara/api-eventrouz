<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminTicketResource as AdminAdminTicketResource;
use App\Http\Resources\TicketResource;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TicketController extends Controller
{

    public function index()
    {

        try {
            $isUser = auth()->user()->role == 'user';

            $query = OrderDetail::whereHas('order', function ($q) use ($isUser) {
                $q->where('status', 'paid');

                if($isUser){
                    $q->where('user_id', auth()->id());
                }
            })->with(['ticketCategory.event']);

            if(!$isUser){
                $query = $query->with('order.user');
            }


            $orders = $query->get();

            $orders->map(function ($order){
                $eventDate = $order?->ticketCategory?->event?->start_at;
                $order->status_event = $eventDate && Carbon::parse($eventDate)->isPast() ? 'Selesai' : 'Mendatang';

                return $order;
            });

            return response()->json([
                'success' => true,
                'message' => 'Tiket berhasil ditampilkan',
                'data' => $isUser ? TicketResource::collection($orders) : AdminAdminTicketResource::collection($orders)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    public function show(string $id)
    {
    
    }

   
}
