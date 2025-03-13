<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - PlayStation Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Midtrans -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $client_key }}"></script>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">PlayStation Rental - Payment</h4>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Booking Summary</h5>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 200px;">Service:</th>
                                    <td>{{ $booking->service->name }}</td>
                                </tr>
                                <tr>
                                    <th>Booking Date:</th>
                                    <td>{{ $booking->booking_date->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Time:</th>
                                    <td>{{ date('H:i', strtotime($booking->start_time)) }} -
                                        {{ date('H:i', strtotime($booking->end_time)) }}</td>
                                </tr>
                                <tr>
                                    <th>Customer:</th>
                                    <td>{{ $booking->customer_name }}</td>
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
                                    <th>Total:</th>
                                    <td><strong>Rp {{ number_format($booking->total_price, 0, ',', '.') }}</strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="d-grid gap-2 mt-4">
                            <button id="pay-button" class="btn btn-primary">Pay Now</button>
                            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('pay-button').onclick = function () {
                // Trigger snap popup
                window.snap.pay('{{ $snap_token }}', {
                    onSuccess: function (result) {
                        window.location.href = '{{ route('bookings.confirmation', $booking->id) }}';
                    },
                    onPending: function (result) {
                        window.location.href = '{{ route('bookings.confirmation', $booking->id) }}';
                    },
                    onError: function (result) {
                        alert('Payment failed: ' + result.status_message);
                        window.location.href = '{{ route('bookings.index') }}';
                    },
                    onClose: function () {
                        alert('You closed the payment popup without completing the payment');
                    }
                });
            };
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>