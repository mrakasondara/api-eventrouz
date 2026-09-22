<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartStoreRequest;
use App\Http\Resources\Cart\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if(!$user){
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $cart = Cart::with(['cartItems.ticketCategory.event'])
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Cart berhasil ditampilkan.',
            'data' => $cart ? new CartResource($cart) : null
        ], 200);
    }

    public function store(CartStoreRequest $request)
    {
        $user = auth()->user();

        if(!$user){
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $data = $request->validated();

        //  check is ticket available

        $ticketCategory = TicketCategory::where('id', $data['ticket_category_id'])->firstOrFail();
        $quota = $ticketCategory['quota'];

        if($quota < $data['total_ticket']){
            return response()->json([
                'success' => true,
                'message' => 'Stok tidak tersedia.',
            ], 400);
        }


        DB::beginTransaction();
        try {           
            $userCart = Cart::firstOrCreate([
                    'user_id' => $user->id
            ]);

            // check if ticket category exist in cart items

            $cartItem = CartItem::where('cart_id', $userCart['id'])->where('ticket_category_id', $data['ticket_category_id'])->first();

            if($cartItem){
                $cartItem->increment('total_ticket', $data['total_ticket']);
            } else {
                CartItem::create([
                    'cart_id' => $userCart['id'],
                    'ticket_category_id' => $data['ticket_category_id'],
                    'event_ticket_date' => $data['event_ticket_date'] ?? null,
                    'total_ticket' => $data['total_ticket']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cart Item berhasil ditambah.',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi Kesalahan',
             ], 500);
        }
    }

    public function remove(Request $request, $id)
    {
        $user = auth()->user();

        if(!$user){
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        
        try {
            // check is cart exist
            $deleted = CartItem::find($id)->delete();

            if(!$deleted){
                return response()->json([
                    'success' => false,
                    'message' => 'Cart Item tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart Item berhasil dihapus',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something error!',
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $user = auth()->user();

        if(!$user){
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        
        try {
            // check is cart exist
            $record = Cart::where('user_id', $user->id)->first();

            if(!$record){
                return response()->json([
                    'success' => false,
                    'message' => 'Cart tidak ditemukan',
                ], 404);
            }

            CartItem::where('cart_id', $record['id'])->delete();
            $record->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart berhasil dihapus',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something error!',
            ], 500);
        }
    }
}
