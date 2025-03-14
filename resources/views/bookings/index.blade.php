<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PS Rental Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">PlayStation Rental Booking</h3>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
                            @csrf
                            <!-- Service Selection -->
                            <div class="mb-3">
                                <label for="service_id" class="form-label">Select Service</label>
                                <select name="service_id" id="service_id" class="form-select" required>
                                    <option value="">Select a service</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" data-price="{{ $service->price }}">
                                            {{ $service->name }} (Rp {{ number_format($service->price, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Calendar -->
                            <div class="mb-3">
                                <label for="booking_date" class="form-label">Booking Date</label>
                                <input type="text" name="booking_date" id="booking_date" class="form-control" required>
                            </div>

                            <!-- Time Selection -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_time" class="form-label">Start Time</label>
                                    <input type="time" name="start_time" id="start_time" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="end_time" class="form-label">End Time</label>
                                    <input type="time" name="end_time" id="end_time" class="form-control" required>
                                    <div class="invalid-feedback">End time harus setelah start time.</div>
                                </div>
                            </div>

                            <!-- Customer Information -->
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Your Name</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="customer_email" class="form-label">Email Address</label>
                                <input type="email" name="customer_email" id="customer_email" class="form-control"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="customer_phone" class="form-label">Phone Number</label>
                                <input type="text" name="customer_phone" id="customer_phone" class="form-control">
                            </div>

                            <!-- Price Calculation -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">Price Calculation</div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-6">Base Price:</div>
                                        <div class="col-6 text-end" id="basePrice">Rp 0</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6">Weekend Surcharge:</div>
                                        <div class="col-6 text-end" id="weekendSurcharge">Rp 0</div>
                                    </div>
                                    <div class="row fw-bold">
                                        <div class="col-6">Total Price:</div>
                                        <div class="col-6 text-end" id="totalPrice">Rp 0</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Book Now</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Date picker
            flatpickr("#booking_date", {
                minDate: "today",
                dateFormat: "Y-m-d",
                onChange: calculatePrice
            });

            // Time pickers
            flatpickr("#start_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                minuteIncrement: 30,
                onChange: function () {
                    // Set end time to start time + 1 hour by default
                    const startTime = document.getElementById('start_time').value;
                    if (startTime) {
                        const [hours, minutes] = startTime.split(':').map(Number);
                        let newHours = hours + 1;
                        if (newHours > 23) newHours = 23;
                        const endTime = `${String(newHours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;

                        const endTimePicker = document.getElementById('end_time')._flatpickr;
                        endTimePicker.setDate(endTime);
                    }
                }
            });

            flatpickr("#end_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                minuteIncrement: 30
            });

            // Service selection
            document.getElementById('service_id').addEventListener('change', calculatePrice);
        });

        function calculatePrice() {
            const serviceId = document.getElementById('service_id').value;
            const bookingDate = document.getElementById('booking_date').value;

            if (!serviceId || !bookingDate) return;

            fetch('{{ route('bookings.calculate-price') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    service_id: serviceId,
                    booking_date: bookingDate
                })
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('basePrice').textContent = data.formatted_base_price;
                    document.getElementById('weekendSurcharge').textContent = data.formatted_weekend_surcharge;
                    document.getElementById('totalPrice').textContent = data.formatted_total_price;
                })
                .catch(error => {
                    console.error('Error calculating price:', error);
                });
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');

            // Inisialisasi Flatpickr
            const startPicker = flatpickr(startTimeInput, {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                minuteIncrement: 30,
                onChange: function (selectedDates, dateStr) {
                    validateTime();
                }
            });

            const endPicker = flatpickr(endTimeInput, {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                minuteIncrement: 30,
                onChange: function (selectedDates, dateStr) {
                    validateTime();
                }
            });

            function validateTime() {
                let startTime = startTimeInput.value;
                let endTime = endTimeInput.value;

                if (startTime && endTime) {
                    if (endTime <= startTime) {
                        endTimeInput.setCustomValidity("End time harus lebih dari start time.");
                        endTimeInput.classList.add("is-invalid");
                    } else {
                        endTimeInput.setCustomValidity("");
                        endTimeInput.classList.remove("is-invalid");
                    }
                }
            }
        });
    </script>
</body>

</html>