<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Resources\Admin\AdminOrderResource;
use App\Http\Resources\Admin\AdminOrderWithPaymentResource;
use App\Http\Resources\Admin\AdminUnpaidOrderResource;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderWithPaymentResource;
use App\Http\Resources\UnpaidOrderResource;
use App\Models\Order;
use App\Models\TicketCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

  public function index(Request $request)
  {

    try {
        $query = Order::with(['user', 'ordersDetails.ticketCategory.event']);

        $isUser = auth()->user()->role === 'user';

        $query->when($isUser, function ($q){
            $q->where('user_id', auth()->id());
        });

        $query->when($request->filled('status'), function ($q) use ($request){
            $q->where('status', $request->status);
        });
        
        $query->orderBy('created_at','desc');

        $query->when($request->filled('limit'), function ($q) use ($request){
            $q->limit((int) $request->limit);
        });

        $orders = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil ditampilkan',
            'data' => $isUser ? OrderResource::collection($orders) : AdminOrderResource::collection($orders)
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan',
        ], 500);
    }
  }

  public function store(OrderStoreRequest $request)
  {
    $validated = $request->validated();


    // only user can order ticket
    if(auth()->user()->role == 'admin'){
        return response()->json([
            'success' => false,
            'message' => 'Hanya user yang memiliki akses.'
        ],403);
    }

    // check if user have pending order

    $pendingOrder = Order::where('user_id', auth()->id())->where('status', 'pending')->exists();

    if($pendingOrder){
        return response()->json([
            'success' => false,
            'message' => 'Silahkan selesaikan pembayaran sebelumnya terlebih dahulu!',
        ], 400);
    }

    DB::beginTransaction();

    try {
        $totalPrice = 0;
        $ordersDetailsData = [];

        foreach ($validated['items'] as $item) {
            // cek category
            $category = TicketCategory::lockForUpdate()->findOrFail($item['ticket_category_id']);

            // cek stock
            if($category->quota < $item['quantity']){
                DB::rollBack();
                return response()->json([
                    'message' => "Stok tiket {category->name} tidak mencukupi"
                ],422);
            }

            // hitung total harga

            $subTotal = $category->price * $item['quantity'];
            $totalPrice += $subTotal;

            $ordersDetailsData[] = [
                'ticket_category_id' => $item['ticket_category_id'],
                'quantity' => $item['quantity'],
                'price' => $category->price,
                'event_ticket_date' => $item['event_ticket_date'],
            ];

            // kurangi stok tiket dan tambah reserved
            $category->decrement('quota', $item['quantity']);
            $category->increment('reserved', $item['quantity']);

        }
        
        $order = Order::create([
            'user_id' => auth()->id(),
            'total_price' => $totalPrice,
            'status' => 'pending'
        ]);

        // simpan semua hasil

        $order->ordersDetails()->createMany($ordersDetailsData);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dibuat',
            'data' => new OrderResource($order)
        ],201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
           'success' => false,
           'message' => 'Terjadi Kesalahan',
           'error' => $e->getMessage()
        ], 500);
    }
  }

  public function show($id) {
    try {

        $isUser = auth()->user()->role === 'user';

        if($isUser){
            $order = Order::with(['ordersDetails.ticketCategory.event','payment'])
                ->where('user_id', auth()->id())
                ->findOrFail($id);
        } else {
            $order = Order::with(['user','ordersDetails.ticketCategory.event','payment'])
                ->findOrFail($id);
        }
        

        if($order['status'] == 'paid'){
            if($isUser){
                $resource = new OrderWithPaymentResource($order);
            } else {
                $resource = new AdminOrderWithPaymentResource($order);
            }
        } else {
            if($isUser){
                $resource = new UnpaidOrderResource($order);
            } else {
                $resource = new AdminUnpaidOrderResource($order);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Order detail berhasil ditampilkan',
            'data' =>  $resource,
        ],200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Order tidak ditemukan atau Anda tidak memiliki akses.',
            'data'    => null
        ], 404);
    }
  }
}
