<?php
// app/Models/Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'service_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'booking_date',
        'start_time',
        'end_time',
    ];
    
    protected $casts = [
        'booking_date' => 'date',
    ];
    
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    
    public function calculateTotalPrice()
    {
        // Pastikan base_price sudah diatur
        if (!$this->base_price) {
            $this->base_price = $this->service->price ?? 0;
        }
        
        // Hitung weekend surcharge
        $bookingDate = Carbon::parse($this->booking_date);
        if ($bookingDate->isWeekend()) {
            $this->weekend_surcharge = 50000; // Sesuai dengan logic di BookingController
        } else {
            $this->weekend_surcharge = 0;
        }
        
        // Total price
        $this->total_price = $this->base_price + $this->weekend_surcharge;
        
        return $this;
    }
}