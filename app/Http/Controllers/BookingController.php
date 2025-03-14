<?php
// app/Http/Controllers/BookingController.php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function index()
    {
        $services = Service::all();
        return view('bookings.index', compact('services'));
    }

    public function calculatePrice(Request $request)
    {
        $service = Service::findOrFail($request->service_id);
        $bookingDate = Carbon::parse($request->booking_date);
        $basePrice = $service->price;
        $weekendSurcharge = 0;
        
        // Check if booking date is weekend
        if ($bookingDate->isWeekend()) {
            $weekendSurcharge = 50000;
        }
        
        $totalPrice = $basePrice + $weekendSurcharge;
        
        return response()->json([
            'base_price' => $basePrice,
            'weekend_surcharge' => $weekendSurcharge,
            'total_price' => $totalPrice,
            'formatted_base_price' => 'Rp ' . number_format($basePrice, 0, ',', '.'),
            'formatted_weekend_surcharge' => 'Rp ' . number_format($weekendSurcharge, 0, ',', '.'),
            'formatted_total_price' => 'Rp ' . number_format($totalPrice, 0, ',', '.'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);
        
        // Check for existing booking with the same details
        $existingBooking = Booking::where('customer_email', $request->customer_email)
            ->where('booking_date', $request->booking_date)
            ->where('start_time', $request->start_time)
            ->where('service_id', $request->service_id)
            ->first();
        
        if ($existingBooking) {
            // If there's already a booking, redirect to its payment or confirmation page
            if ($existingBooking->status === 'paid') {
                return redirect()->route('bookings.confirmation', $existingBooking->id)
                    ->with('info', 'You already have a confirmed booking for this time.');
            } else {
                return redirect()->route('bookings.confirmation', $existingBooking->id)
                    ->with('info', 'You already have a pending booking for this time.');
            }
        }

        // Continue with booking creation if no existing booking found
        $service = Service::findOrFail($request->service_id);
        $basePrice = $service->price;
        
        $booking = new Booking($request->all());
        $booking->base_price = $basePrice;
        $booking->calculateTotalPrice();
        $booking->save();

        // Create Midtrans transaction
        $midtransResponse = $this->midtransService->createTransaction($booking);

        if (!$midtransResponse['success']) {
            return redirect()->back()->with('error', 'Failed to process payment: ' . $midtransResponse['message']);
        }

        // Simpan snap token ke booking
        $booking->snap_token = $midtransResponse['snap_token'];
        $booking->save();

        return view('bookings.payment', [
            'booking' => $booking,
            'snap_token' => $booking->snap_token,
            'client_key' => config('midtrans.client_key'),
        ]);
    }

    public function confirmation($id)
    {
        $booking = Booking::findOrFail($id);
        return view('bookings.confirmation', compact('booking'));
    }

    public function callback(Request $request)
    {
        try {
            // Log request untuk debugging dengan lebih detail
            Log::info('Midtrans Callback Received', ['data' => $request->all()]);

            // Verifikasi tanda tangan
            $serverKey = config('midtrans.server_key');
            $expectedSignature = hash("sha512", 
                $request->order_id . 
                $request->status_code . 
                $request->gross_amount . 
                $serverKey
            );

            if (!hash_equals($expectedSignature, $request->signature_key)) {
                Log::warning('Invalid Signature Key', [
                    'expected' => $expectedSignature,
                    'received' => $request->signature_key
                ]);
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
            }

            // Cari booking berdasarkan payment_id
            $booking = Booking::where('payment_id', $request->order_id)->first();

            if (!$booking) {
                Log::error('Booking not found for order_id: ' . $request->order_id);
                return response()->json(['success' => false, 'message' => 'Booking not found'], 404);
            }

            // Simpan semua data dari Midtrans sebagai JSON
            $paymentData = $request->all();
            if (!empty($paymentData)) {
                $booking->payment_data = json_encode($paymentData);
            } else {
                Log::warning('Empty payment data received');
            }

            // Update status pembayaran
            $booking->payment_status = $request->transaction_status;

            // Pemrosesan status lebih detail
            if (in_array($request->transaction_status, ['capture', 'settlement'])) {
                $booking->status = 'paid';
                Log::info('Payment completed for booking #' . $booking->id);
            } elseif (in_array($request->transaction_status, ['cancel', 'deny', 'expire'])) {
                $booking->status = 'cancelled';
                Log::info('Payment cancelled for booking #' . $booking->id);
            } elseif ($request->transaction_status == 'pending') {
                $booking->status = 'pending';
                Log::info('Payment still pending for booking #' . $booking->id);
            }

            $booking->save();

            Log::info('Booking updated successfully', [
                'order_id' => $request->order_id,
                'status' => $booking->status,
                'payment_data_saved' => !empty($booking->payment_data)
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Midtrans Callback Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function updatePaymentStatus($id)
    {
        $booking = Booking::findOrFail($id);
        
        // Cek status pembayaran di Midtrans
        try {
            // Implementasi panggilan API status Midtrans disini
            // Untuk testing, kita anggap pembayaran berhasil
            $booking->status = 'paid';
            $booking->payment_status = 'settlement';
            $booking->save();
            
            return redirect()->route('bookings.confirmation', $booking->id)
                ->with('success', 'Payment status updated successfully');
        } catch (\Exception $e) {
            return redirect()->route('bookings.confirmation', $booking->id)
                ->with('error', 'Failed to update payment status: ' . $e->getMessage());
        }
    }

}