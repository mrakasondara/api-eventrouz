<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminTicketResource as AdminAdminTicketResource;
use App\Http\Resources\checkInTicketResource;
use App\Http\Resources\TicketResource;
use App\Models\OrderDetail;
use App\Models\TicketCategory;
use Carbon\Carbon;
use DateTime;
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
                $eventDate = $order?->ticketCategory?->event?->end_at;
                $order->status_event = $eventDate && Carbon::parse($eventDate)->isPast() ? 'selesai' : 'mendatang';

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


    public function checkIn(string $ticketCode)
    {
        if(auth()->user()->role == 'user'){
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        try {
            $query = OrderDetail::where('ticket_code', $ticketCode)->where('is_checkin', false)->with(['ticketCategory','order.user']);
            
            
            $ticket = $query->first();

            if(!$ticket){
                return response()->json([
                    'success' => false,
                    'message' => 'Check In gagal',
                ]);
            }

            $ticket['is_checkin'] = true;
            $ticket['checkin_time'] = now()->format('Y-m-d H:i:s');
            $ticket->save();

            $getTicketByEvent = TicketCategory::where('event_id', $ticket->ticketCategory->event_id)->get();

            $totalReserved = $getTicketByEvent->reduce(function ($carry, $item) {
                return $carry + $item['reserved'];
            }, 0);

            $getCheckin= OrderDetail::where('is_checkin', true)
            ->whereHas('ticketCategory.event', function ($query) use($ticket) {
                $query->where('id', $ticket->ticketCategory->event_id);
            })
            ->with(['ticketCategory'])
            ->get();

            $totalCheckin = $getCheckin->reduce(function ($carry, $item) {
                return $carry + $item->is_checkin;
            }, 0);
            
            $checkInRemaining = $totalReserved - $totalCheckin;

            $ticket['total_checkedin'] = $totalCheckin;
            $ticket['remaining'] = $checkInRemaining;

            return response()->json([
                'success' => true,
                'message' => 'Check In tiket berhasil',
                'data' => new checkInTicketResource($ticket) 
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check In gagal',
                'data' => $e->getMessage()
            ]);
        }
    }

   
}
