<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
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

            $vaNumber = null;
            $bank = null;
            if (isset($notif->va_numbers[0])) {
                $vaNumber = $notif->va_numbers[0]->va_number;
                $bank = $notif->va_numbers[0]->bank;
            }

            $isPaid = ($transactionStatus == 'capture' && $fraud != 'challenge') || $transactionStatus == 'settlement';

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

            if($notif->transaction_id){
                Payment::updateOrCreate(
                    ['midtrans_transaction_id' => $notif->transaction_id],
                    [
                        'order_id' => $order['id'],
                        'user_id' => $order['user_id'],
                        'gross_amount' => $order['total_price'],
                        'payment_type' => $notif->payment_type,
                        'bank' => $bank ?? $notif->issuer,
                        'va_number' => $vaNumber,
                        'payment_status' => $notif->transaction_status,
                        'fraud_status' => $notif->fraud_status ?? null,
                        'paid_at' => $notif->transaction_status === 'settlement' ? now() : null,
                        'snap_response' => json_encode($notif->getResponse())
                    ]
                    );
            }

            if($isPaid){
                $orderDetail = OrderDetail::where('order_id', $orderId)->first();
                $orderDetail['ticket_code'] = 'ETZ-'.Rand(10000,99999);

                $orderDetail->save();
            }

            return response()->json(['message' => 'Notification processed successfully', 'data' => $notif->getResponse()],200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing notification',
                'error' => $e->getMessage()
            ], 500);
        }
        
    }
}
