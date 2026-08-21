<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Resources\Admin\AdminOrderResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

  public function index(Request $request)
  {

    try {
        $query = Order::with(['user']);

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
            'data' => null
        ], 500);
    }
  }

  public function store(OrderStoreRequest $request)
  {
    $validated = $request->validated();

    DB::beginTransaction();

    try {
        $totalPrice = 0;
        $ordersDetailsData = [];

        // only user can order ticket
        if(auth()->user()->role == 'admin'){
            return response()->json([
                'success' => false,
                'message' => 'Hanya user yang memiliki akses.'
            ],403);
        }

        foreach ($validated['items'] as $item) {
            // cek category
            $category = TicketCategory::lockForUpdate()->findOrFail($item['ticket_category_id']);

            // cek stock
            if($category->quota < $item['quantity']){
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
                'ticket_code' => 'ETZ-'.Rand(10000,99999),
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
        if(auth()->user()->role == 'admin'){
            $order = Order::with(['ordersDetails.ticketCategory.event'])
                ->findOrFail($id);
        } else {
            $order = Order::with(['ordersDetails.ticketCategory.event'])
                ->where('user_id', auth()->id())
                ->findOrFail($id);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Order detail berhasil ditampilkan',
            'data' => new OrderResource($order)
        ],200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Order tidak ditemukan atau Anda tidak memiliki akses.',
            'data'    => null
        ], 404);
    }
  }
}
