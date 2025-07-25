
@extends('layouts.main')

@section('content')

<style>
    .product-box:hover {
        border-color: #007bff;
        background-color: #f1f9ff;
    }
</style>
    <h1>Order Details</h1>

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class='card'>
                <div class='card-body'>

                    @if ($item->status === 'pending')
    <form action='{{ route('orderitems.store') }}' method='POST'>
        @csrf

        <input type='hidden' name='orders_id' value="{{ $item->id }}">
        <input type='hidden' name='orders_users_id' value="{{ $item->users->id ?? "0" }}">

        <div class='mb-2 form-group'>
            <label>Table Number</label>
            <input type='text' class='form-control' name='table_number' value="{{ $item->table_number }}" required>
        </div>

        {{-- Product Selection Boxes --}}
        <div class="mb-3 row">
            @foreach (App\Models\Products::all() as $product)
                <div class="mb-2 col-md-3">
                    <div class="card product-box"
                        data-id="{{ $product->id }}"
                        data-name="{{ $product->name }}"
                        data-price="{{ $product->price }}"
                        style="cursor: pointer; border: 2px solid #ccc;">
                        <div class="text-center card-body">
                            <h5>{{ $product->name }}</h5>
                            <p>₱{{ number_format($product->price, 2) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Added Items Container --}}
        <div id="selected-products"></div>

        <div class="mt-3">
            <strong>Total: ₱<span id="total-price">0.00</span></strong>
        </div>

        <button type='submit' class='mt-3 btn btn-primary'>Send to Kitchen</button>
    </form>
@endif


                    @if ($item->status === 'preparing')

                        <h3 class="mt-2 fw-bold" style="color: #1A3700">{{ ucfirst($item->status) }}...</h3>
                        <div style="display: flex" class="mb-2">
                            <div
                                        class="timer"
                                        id="live-timer"
                                        data-updated="{{ $item->sent_to_kitchen_at }}"
                                        style="
                                            background-color: #000000;
                                            color: #C49C74;
                                            font-family: 'Courier New', monospace;
                                            font-size: 24px;
                                            padding: 10px 20px;
                                            border-radius: 8px;
                                            display: inline-block;
                                            letter-spacing: 2px;
                                            min-width: 120px;
                                            text-align: center;
                                            box-shadow: 0 0 10px #C49C74;
                                        "
                                    ></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr style="text-align: center;">
                                        <th colspan="4"><h2>Table Number: {{ $item->table_number }}</h2></th>
                                    </tr>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (App\Models\Orderitems::where('orders_id', $item->id)->get() as $orderItem)
                                        <tr>
                                            <td>{{ $orderItem->products->name ?? "no data" }}</td>
                                            <td>{{ $orderItem->quantity ?? "no data" }}</td>
                                            <td>₱ {{ Smark\Smark\Math::convertToMoneyFormat($orderItem->total) ?? "no data" }}</td>
                                            <td>₱ {{ Smark\Smark\Math::convertToMoneyFormat($orderItem->subtotal) ?? "no data" }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <h1 class="fw-bold">Total: <span class="text-success">₱ {{ Smark\Smark\Math::convertToMoneyFormat($item->total) }}</span></h1>

                            <a href="{{ url('/edit-orders/'.$item->id) }}"><button class="btn btn-outline-danger"><i class="fas fa-edit"></i> Edit Order</button></a>
                            <a href="{{ url('/mark-as-done-order/'.$item->id) }}"><button class="btn btn-outline-success"><i class="fas fa-check"></i> Mark As Done</button></a>
                        </div>
                    @endif

                    @if ($item->status === 'done')
                        <h3 class="mt-2 fw-bold" style="color: #1A3700">
                            {{ ucfirst($item->status) }}...
                        </h3>

                        <div style="display: flex; gap: 10px;" class="mb-2">
                            <div
                                id="duration-timer"
                                class="timer"
                                data-start="{{ $item->sent_to_kitchen_at }}"
                                data-end="{{ $item->done_at }}"
                                style="
                                    background-color: #000000;
                                    color: #C49C74;
                                    font-family: 'Courier New', monospace;
                                    font-size: 24px;
                                    padding: 10px 20px;
                                    border-radius: 8px;
                                    display: inline-block;
                                    letter-spacing: 2px;
                                    min-width: 120px;
                                    text-align: center;
                                    box-shadow: 0 0 10px #C49C74;
                                "
                            ></div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr style="text-align: center;">
                                        <th colspan="4"><h2>Table Number: {{ $item->table_number }}</h2></th>
                                    </tr>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (App\Models\Orderitems::where('orders_id', $item->id)->get() as $orderItem)
                                        <tr>
                                            <td>{{ $orderItem->products->name ?? 'no data' }}</td>
                                            <td>{{ $orderItem->quantity ?? 'no data' }}</td>
                                            <td>₱ {{ Smark\Smark\Math::convertToMoneyFormat($orderItem->total) ?? 'no data' }}</td>
                                            <td>₱ {{ Smark\Smark\Math::convertToMoneyFormat($orderItem->subtotal) ?? 'no data' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <h1 class="fw-bold">
                                Total:
                                <span class="text-success">
                                    ₱ {{ Smark\Smark\Math::convertToMoneyFormat($item->total) }}
                                </span>
                            </h1>
                        </div>
                    @endif


                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class='card'>
                <div class='card-body'>
                    <div class='table-responsive'>
                        <table class='table'>

                            <tr>
                                <th>Status</th>
                                <td>{{ $item->status }}</td>
                            </tr>

                            <tr>
                                <th>Sent To Kitchen At</th>
                                <td>
                                    {{ !empty($item->sent_to_kitchen_at) ? Smark\Smark\Dater::humanReadableDateWithDayAndTime($item->sent_to_kitchen_at) : '' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Done At</th>
                                <td>
                                    {{ !empty($item->done_at) ? Smark\Smark\Dater::humanReadableDateWithDayAndTime($item->done_at) : '' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Counter Staff</th>
                                <td>{{ $item->users->name ?? "no data" }}</td>
                            </tr>

                            <tr>
                                <th>Created At</th>
                                <td>{{ Smark\Smark\Dater::humanReadableDateWithDayAndTime($item->created_at) }}</td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td>{{ Smark\Smark\Dater::humanReadableDateWithDayAndTime($item->updated_at) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href='{{ route('orders.index') }}' class='btn btn-primary'>Back to List</a>

    <script src='{{ url('assets/jquery/jquery.min.js') }}'></script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    let total = 0;

    // Add product box click handler
    document.querySelectorAll('.product-box').forEach(box => {
        box.addEventListener('click', () => {
            const productId = box.dataset.id;
            const productName = box.dataset.name;
            const productPrice = parseFloat(box.dataset.price);

            const container = document.getElementById('selected-products');

            // Prevent duplicates
            if (container.querySelector(`input[value="${productId}"]`)) {
                alert(`${productName} is already added.`);
                return;
            }

            const group = document.createElement('div');
            group.className = 'row mb-2 align-items-end selected-item';

            group.innerHTML = `
                <div class="col-md-6">
                    <input type="hidden" name="product_id[]" value="${productId}">
                    <input type="text" class="form-control-plaintext" value="${productName}" readonly>
                </div>
                <div class="col-md-3">
                    <input type="number" name="quantity[]" class="form-control quantity-input" min="1" value="1" required>
                </div>
                <div class="col-md-2">
                    <span class="product-price">₱${productPrice.toFixed(2)}</span>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-item">X</button>
                </div>
            `;

            container.appendChild(group);
            updateTotal();

            // Quantity change triggers total update
            group.querySelector('.quantity-input').addEventListener('input', updateTotal);

            // Remove item
            group.querySelector('.remove-item').addEventListener('click', () => {
                group.remove();
                updateTotal();
            });
        });
    });

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.selected-item').forEach(group => {
            const priceText = group.querySelector('.product-price')?.textContent.replace(/[₱,]/g, '') || '0';
            const price = parseFloat(priceText);
            const quantity = parseInt(group.querySelector('.quantity-input')?.value || '0');
            total += price * quantity;
        });

        // Get selected discount value
        const discountRadio = document.querySelector('input[name="discount"]:checked');
        const discountPercent = discountRadio ? parseFloat(discountRadio.value) : 0;

        // Calculate discount
        const discountAmount = (total * discountPercent) / 100;
        const discountedTotal = total - discountAmount;

        // Update the displayed price
        if (totalPriceEl) {
            totalPriceEl.textContent = discountedTotal.toFixed(2);
        }

        // Log both values to the console
        console.log("Original Total:", total.toFixed(2));
        console.log("Discount:", discountPercent + "%");
        console.log("Discounted Total:", discountedTotal.toFixed(2));
    }

    // Submit form with AJAX
    document.querySelector('form').addEventListener('submit', function (e) {
        e.preventDefault();

        const items = [];
        document.querySelectorAll('.selected-item').forEach(group => {
            const productId = group.querySelector('input[name="product_id[]"]').value;
            const quantity = parseInt(group.querySelector('input[name="quantity[]"]').value) || 0;
            const price = parseFloat(group.querySelector('.product-price').textContent.replace(/[₱,]/g, '')) || 0;

            items.push({
                product_id: productId,
                quantity,
                price,
                subtotal: price * quantity
            });
        });

        const tableNumber = document.querySelector('input[name="table_number"]').value;
        const orderId = document.querySelector('input[name="orders_id"]').value;
        const ordersUsersId = document.querySelector('input[name="orders_users_id"]').value;
        const total = items.reduce((acc, item) => acc + item.subtotal, 0);

        $.post('/store-orderitems', {
            tableNumber,
            orderId,
            ordersUsersId,
            items,
            total,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }, function (res) {
            window.location.href = '/show-orders/' + orderId;
        }).fail(err => {
            console.error('Submission failed:', err);
        });
    });

    // Live order timer
    function startTimer() {
        const timerElement = document.getElementById('live-timer');
        const updatedAt = timerElement?.getAttribute('data-updated');
        if (!updatedAt) return;

        const startTime = new Date(updatedAt).getTime();

        function updateTimer() {
            const now = Date.now();
            const diff = now - startTime;

            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            timerElement.textContent =
                (hours ? hours + 'h ' : '') +
                (minutes < 10 ? '0' : '') + minutes + 'm ' +
                (seconds < 10 ? '0' : '') + seconds + 's';
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    }

    // Static timer for completed orders
    function showStaticDuration() {
        const durationElement = document.getElementById('duration-timer');
        if (!durationElement) return;

        const start = new Date(durationElement.getAttribute('data-start')).getTime();
        const end = new Date(durationElement.getAttribute('data-end')).getTime();

        if (isNaN(start) || isNaN(end) || end <= start) return;

        const diff = end - start;

        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        durationElement.textContent =
            (hours ? hours + 'h ' : '') +
            (minutes < 10 ? '0' : '') + minutes + 'm ' +
            (seconds < 10 ? '0' : '') + seconds + 's';
    }

    startTimer();
    showStaticDuration();
});
</script>


@endsection
