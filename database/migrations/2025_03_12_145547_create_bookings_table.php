<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Perbaiki migrasi 2025_03_12_145547_create_bookings_table.php
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('base_price', 10, 2);
            $table->decimal('weekend_surcharge', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);
            $table->string('status')->default('pending'); // pending, paid, cancelled
            $table->string('payment_id')->nullable(); // midtrans order id
            $table->string('payment_status')->nullable();
            $table->text('payment_data')->nullable(); // json data from midtrans
            $table->string('snap_token')->nullable(); // Jangan hapus ini!
            $table->timestamps();
        });        
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
