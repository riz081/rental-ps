<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">PlayStation Rental Booking - Confirmation</h4>
                    </div>
                    <div class="card-body">
                        @if($booking->status == 'paid')
                            <div class="alert alert-success">
                                <h5 class="alert-heading">Payment Successful!</h5>
                                <p>Your booking has been confirmed. Thank you for your payment.</p>
                            </div>
                        @elseif($booking->status == 'pending')
                            <div class="alert alert-warning">
                                <h5 class="alert-heading">Payment Pending</h5>
                                <p>We're waiting for your payment to be confirmed. You will receive an email once the
                                    payment is completed.</p>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <h5 class="alert-heading">Booking Created</h5>
                                <p>Your booking has been created. Please complete the payment to confirm your reservation.
                                </p>
                            </div>
                        @endif

                        <h5 class="card-title mt-4">Booking Details</h5>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 200px;">Booking Reference:</th>
                                    <td><strong>{{ $booking->payment_id ?? 'BOOK-' . $booking->id }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Service:</th>
                                    <td>{{ $booking->service->name }}</td>
                                </tr>
                                <tr>
                                    <th>Customer Name:</th>
                                    <td>{{ $booking->customer_name }}</td>
                                </tr>
                                <tr>
                                    <th>Customer Email:</th>
                                    <td>{{ $booking->customer_email }}</td>
                                </tr>
                                <tr>
                                    <th>Customer Phone:</th>
                                    <td>{{ $booking->customer_phone ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <th>Booking Date:</th>
                                    <td>{{ $booking->booking_date->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Time:</th>
                                    <td>{{ date('H:i', strtotime($booking->start_time)) }} -
                                        {{ date('H:i', strtotime($booking->end_time)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Base Price:</th>
                                    <td>Rp {{ number_format($booking->base_price, 0, ',', '.') }}</td>
                                </tr>
                                @if($booking->weekend_surcharge > 0)
                                    <tr>
                                        <th>Weekend Surcharge:</th>
                                        <td>Rp {{ number_format($booking->weekend_surcharge, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Total Price:</th>
                                    <td><strong>Rp {{ number_format($booking->total_price, 0, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($booking->status == 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($booking->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($booking->status == 'cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($booking->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <a href="{{ route('bookings.update-payment', $booking->id) }}" class="btn btn-warning">
                            Check Payment Status
                        </a>

                        @if($booking->status == 'pending')
                            <div class="d-grid gap-2 mt-4">
                                <a href="{{ route('bookings.index') }}" class="btn btn-primary">Back to Home</a>
                            </div>
                        @else
                            <div class="d-grid gap-2 mt-4">
                                <a href="{{ route('bookings.index') }}" class="btn btn-outline-primary">Back to Home</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>