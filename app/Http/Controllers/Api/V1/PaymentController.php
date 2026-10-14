<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    public function createPayment(Request $request, $id)
    {
        if(auth()->user()->role == 'admin'){
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $query = Order::where('id', $id)->where('user_id', auth()->id());

        $order = $query->first();

        if(!$order){
            return response()->json([
                'success' => false,
                'message' => 'Id tidak ditemukan.',
            ], 404);
        };
        
        Config::$overrideNotifUrl = config('services.midtrans.override_url');

        $params = [
            'transaction_details' => [
                'order_id' => $id,
                'gross_amount' => $order['total_price']
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email
            ],
        ];
        $snapToken = Snap::getSnapToken($params);

        return response()->json([
            'success' => true,
            'snapToken' => $snapToken 
        ]);
    }

    public function handleNotification(Request $request)
    {
        try {
            $notif = new Notification();

            $transactionStatus = $notif->transaction_status ?? null;
            $type = $notif->payment_type ?? null;
            $orderId = $notif->order_id ?? null;
            $fraud = $notif->fraud_status ?? null;

            $order = Order::where('id', $orderId)->first();

            if(!$order){
                return response()->json([
                    'message' => 'Order not found',
                ], 404);
            }

            if($transactionStatus == 'capture'){
                if($type == 'credit_card'){
                    $order->status = ($fraud == 'challenge') ? 'challenge' : 'paid';
                }
            } elseif ($transactionStatus == 'settlement'){
                $order->status = 'paid';
            } elseif ($transactionStatus == 'pending'){
                $order->status = 'pending';
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])){
                $order->status = 'failed';
            }

            $order->save();

            return response()->json(['message' => 'Notification processed successfully', 'data' => $notif->getResponse()],200);
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error processing notification',
                'error' => $e->getMessage()
            ], 500);
        }
        
    }
}
