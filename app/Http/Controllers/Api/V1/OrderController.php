<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
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
        $query = Order::query();

        $isUser = auth()->user()->role == 'user';

        if($isUser){
            $query->where('user_id', auth()->user()->id);
        }

        if($request->status){
            $query->where('status', $request->status);
        }

        $orders = $query->get();

        return response()->json([
            'message' => 'Order berhasil ditampilkan',
            'data' => $isUser ? $orders : OrderResource::collection($orders)
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
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
            'message' => 'Order berhasil dibuat',
            'data' => new OrderResource($order)
        ],201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
           'message' => 'Terjadi Kesalahan',
           'error' => $e->getMessage()
        ], 500);
    }
  }
}
