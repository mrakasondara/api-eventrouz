<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
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

        $params = [
            'transaction_details' => [
                'order_id' => $id,
                'gross_amount' => $order['total_price']
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email
            ],
            'append_notification_url' => config('services.midtrans.override_url'),
            'override_notification_urls' => [
                config('services.midtrans.override_url')
            ]
        ];
        $snapToken = Snap::getSnapToken($params);

        return response()->json([
            'success' => true,
            'snapToken' => $snapToken 
        ]);
    }

    public function handleNotification(Request $request)
    {
        $notif = new Notification();

        $transactionStatus = $notif->transaction_status;
        $type = $notif->payment_type;
        $orderId = $notif->order_id;
        $fraud = $notif->fraud_status;

        $order = Order::where('id', $orderId)->first();

        if(!$order){
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        if($transactionStatus == 'capture'){
            if($type == 'credit_card'){
                $order->status = ($fraud == 'challenge') ? 'challenge' : 'success';
            }
        } elseif ($transactionStatus == 'settlement'){
            $order->status = 'success';
        } elseif ($transactionStatus == 'pending'){
            $order->status = 'pending';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])){
            $order->status = 'failed';
        }

        $order->save();

        return response()->json(['message' => 'Notification processed successfully']);
    }
}
