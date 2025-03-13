<?php
// app/Services/MidtransService.php

namespace App\Services;

use App\Models\Booking;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(Booking $booking)
    {
        $orderId = 'BOOK-' . $booking->id . '-' . time();
        
        $booking->payment_id = $orderId;
        $booking->save();
        
        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => (int) $booking->total_price,
        ];

        $customerDetails = [
            'first_name' => $booking->customer_name,
            'email' => $booking->customer_email,
            'phone' => $booking->customer_phone,
        ];

        $itemDetails = [
            [
                'id' => 'service-' . $booking->service->id,
                'price' => (int) $booking->base_price,
                'quantity' => 1,
                'name' => $booking->service->name . ' (' . $booking->booking_date->format('d M Y') . ')',
            ]
        ];

        // Add weekend surcharge if applicable
        if ($booking->weekend_surcharge > 0) {
            $itemDetails[] = [
                'id' => 'weekend-surcharge',
                'price' => (int) $booking->weekend_surcharge,
                'quantity' => 1,
                'name' => 'Weekend Surcharge',
            ];
        }

        $params = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        ];

        try {
            // Log parameters untuk debugging
            Log::info('Midtrans Parameters', ['params' => $params]);
            
            $snapToken = Snap::getSnapToken($params);
            
            // Simpan snap token ke database
            $booking->snap_token = $snapToken;
            $booking->save();
            
            return [
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $orderId,
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}