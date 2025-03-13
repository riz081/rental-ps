<?php
// app/Services/MidtransWrapper.php

namespace App\Services;

use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Facades\Log;

class MidtransWrapper 
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function getSnapToken(array $params)
    {
        try {
            return Snap::getSnapToken($params);
        } catch (\Exception $e) {
            // Tangkap error spesifik
            if (strpos($e->getMessage(), 'Undefined array key 10023') !== false) {
                Log::warning('Midtrans error 10023 encountered, attempting to handle gracefully');
                
                // Implementasi fallback, misalnya membuat token pembayaran dummy untuk pengembangan
                if (app()->environment('local', 'development')) {
                    return 'DEV-SNAP-TOKEN-' . time();
                }
                
                // Atau throw error yang lebih informatif
                throw new \Exception('Error creating payment token: API response format error', 500);
            }
            
            // Throw kembali error lainnya
            throw $e;
        }
    }
}