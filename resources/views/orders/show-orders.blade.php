@extends('layouts.main')
@section('content')
<style>
   .product-box:hover {
   border-color: #007bff;
   background-color: #f1f9ff;
   }

   .product-box.selected {
        border: 3px solid #1A3600 !important;
        box-shadow: 0 0 12px #1A3600;
        background: linear-gradient(145deg, #f0fff0, #dff5e1);
        transform: scale(1.02);
        transition: all 0.2s ease-in-out;
    }

   .selected-item {
        background: #FFEAD5; /* brand background */
        border: 2px solid #D49837; /* accent border */
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 10px;
        box-shadow: 0 2px 6px rgba(212, 152, 55, 0.3); /* softer accent glow */
        color:  #000000; /* optional: brand accent text color */
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .selected-item:hover {
        box-shadow: 0 4px 12px rgba(26, 54, 0, 0.25);
        transform: scale(1.01);
    }

    .selected-item input.form-control-plaintext {
        font-weight: bold;
        color: #1A3600;
        font-size: 16px;
        padding-left: 0;
    }

    .selected-item .quantity-input {
        width: 100%;
        border-radius: 6px;
        border: 1px solid #a3c4a3;
        padding: 6px;
        font-size: 15px;
        background-color: #ffffff;
        color: #1A3600;
    }

    .selected-item .product-price {
        font-weight: bold;
        color: #2e7d32; /* nice readable green */
        font-size: 16px;
        display: inline-block;
        padding: 6px 0;
    }

    .selected-item .remove-item {
        background-color: #d9534f;
        border: none;
        font-weight: bold;
        padding: 4px 8px;
        color: white;
        border-radius: 4px;
    }

    .selected-item .remove-item:hover {
        background-color: #c9302c;
    }

</style>
<h1>Order Details</h1>
<div class="row">
   <div class="col-lg-5 col-md-5 col-sm-12">
      <div class='card'>
         <div class='card-body'>
            @if ($item->status === 'pending')
            <form action='{{ route('orderitems.store') }}' method='POST'>
            @csrf
            <input type='hidden' name='orders_id' value="{{ $item->id }}">
            <input type='hidden' name='orders_users_id' value="{{ $item->users->id ?? "0" }}">
            <div class='mb-2 form-group'>
               <label>Table Number</label>
               <input type='number' class='form-control' name='table_number' value="{{ $item->table_number }}" required>
            </div>

            {{-- Added Items Container --}}
            <div id="selected-products">
                @foreach (App\Models\Orderitems::where('orders_id', $item->id)->get() as $orderItem)
                    <div class="mb-2 row align-items-end selected-item" data-id="{{ $orderItem->products_id }}">
                        <div class="col-md-6">
                            <input type="hidden" name="product_id[]" value="{{ $orderItem->products_id }}">
                            <input type="text" class="form-control-plaintext" value="{{ $orderItem->products->name ?? "no data" }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="quantity[]" class="form-control quantity-input" min="1" value="{{ $orderItem->quantity }}" required>
                        </div>
                        <div class="col-md-2">
                            <span class="product-price">₱{{ Smark\Smark\Math::convertToMoneyFormat($orderItem->total) }}</span>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm remove-item">X</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
               <strong>Discount:</strong> <br>
               <small>
                    <input type='radio' name='discount' value="0" checked> No Discount <br>
               </small>
               @forelse (App\Models\Discounts::all() as $discount)
               <small>
                    <input type='radio' name='discount' value="{{ $discount->discount }}"> {{ $discount->name }} - {{ $discount->discount }}% <br>
               </small>
               @empty
                <b>No discount list available...</b>
               @endforelse
            </div>

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
                        <th colspan="5">
                           <h2>Table Number: {{ $item->table_number }}</h2>
                        </th>
                     </tr>
                     <tr>
                        <th>Code</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach (App\Models\Orderitems::where('orders_id', $item->id)->get() as $orderItem)
                     <tr>
                        <td><b>{{ $orderItem->products->product_id ?? "no data" }}</b></td>
                        <td>{{ $orderItem->products->name ?? "no data" }}</td>
                        <td>{{ $orderItem->quantity ?? "no data" }}</td>
                        <td>₱ {{ Smark\Smark\Math::convertToMoneyFormat($orderItem->total) ?? "no data" }}</td>
                        <td>₱ {{ Smark\Smark\Math::convertToMoneyFormat($orderItem->subtotal) ?? "no data" }}</td>
                     </tr>
                     @endforeach
                  </tbody>
               </table>
               {{-- <h1 class="fw-bold">Total: <span class="text-success">₱ {{ Smark\Smark\Math::convertToMoneyFormat($item->total) }}</span></h1> --}}

               <h4 class="fw-bold">
                  Total:
                  <span class="text-success">
                  ₱ {{ Smark\Smark\Math::convertToMoneyFormat($item->total) }}
                  </span>
               </h4>
               @if ($item->discount != 0)
               <h3 class="fw-bold">
                  <span class="text-secondary">Discounted Price (-{{ $item->discount }}%):</span>
                  <span class="text-success">
                  ₱ {{ Smark\Smark\Math::convertToMoneyFormat($item->discounted_price) }}
                  </span>
               </h3>
               @endif
               <a href="{{ url('/edit-orders/'.$item->id.'/'.$item->order_type) }}"><button class="btn btn-outline-danger"><i class="fas fa-edit"></i> Edit Order</button></a>
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
                        <th colspan="4">
                           <h2>Table Number: {{ $item->table_number }}</h2>
                        </th>
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
               <h4 class="fw-bold">
                  Total:
                  <span class="text-success">
                  ₱ {{ Smark\Smark\Math::convertToMoneyFormat($item->total) }}
                  </span>
               </h4>
               @if ($item->discount != 0)
               <h3 class="fw-bold">
                  <span class="text-secondary">Discounted Price (-{{ $item->discount }}%):</span>
                  <span class="text-success">
                  ₱ {{ Smark\Smark\Math::convertToMoneyFormat($item->discounted_price) }}
                  </span>
               </h3>
               @endif
            </div>
            @endif
         </div>
      </div>
   </div>
   <div class="col-lg-7 col-md-7 col-sm-12">
      <div class='card'>
         <div class='card-body'>

            {{-- <h2>{{ App\Models\Types::where('id', $typesId)->value('name') }}</h2> --}}

            {{-- search bar for selected products --}}

            <div class="mb-3 form-group">
                <input type="text" class="form-control search-menu-input" placeholder="Search Menu...">
            </div>

            <div class='table-responsive'>
                @if ($item->status === 'pending')
                    <div class="row" style="height: 70vh; overflow-y: scroll; width: 100%;">
                        @foreach (App\Models\Products::where('types_id', $typesId)->get() as $product)
                            <div class="mb-2 col-md-3">
                                <div class="card product-box"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $product->price }}"
                                    style="
                                        cursor: pointer;
                                        border: 2px solid #f4e2d8;
                                        border-radius: 12px;
                                        background: linear-gradient(145deg, #fff8f1, #ffe8d1);
                                        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                                        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
                                    "
                                    onmouseover="this.style.transform='scale(1.03)'; this.style.boxShadow='0 6px 15px rgba(0,0,0,0.2)';"
                                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 10px rgba(0, 0, 0, 0.1)';"
                                >
                                    <div class="text-center card-body" style="padding: 20px;">
                                        <b style="display: block; font-size: 18px; color: #bf360c;">{{ $product->product_id }}</b>
                                        <b style="display: block; font-size: 12px; color: #4e342e; margin: 5px 0;">{{ $product->name }}</b>
                                        <p style="font-size: 15px; color: #2e7d32; font-weight: bold;">₱{{ number_format($product->price, 2) }}</p>
                                    </div>
                                </div>
                            </div>

{{-- <div class="mb-2 col-md-3">
    <div class="card product-box"
        data-id="{{ $product->id }}"
        data-name="{{ $product->name }}"
        data-price="{{ $product->price }}"
        style="
            cursor: pointer;
            border: 2px solid #A2CFA3; /* soft green border */
            border-radius: 12px;
            background: linear-gradient(145deg, #ecf9ec, #d0e9d0); /* gentle green gradient */
            box-shadow: 0 4px 10px rgba(26, 54, 0, 0.15);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        "
        onmouseover="this.style.transform='scale(1.03)'; this.style.boxShadow='0 6px 15px rgba(26, 54, 0, 0.25)';"
        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 10px rgba(26, 54, 0, 0.15)';"
    >
        <div class="text-center card-body" style="padding: 20px;">
            <b style="display: block; font-size: 12px; color: #bf360c;">{{ $product->product_id }}</b>
            <b style="display: block; font-size: 12px; color: #4e342e; margin: 5px 0;">{{ $product->name }}</b>
            <p style="font-size: 15px; color: #2e7d32; font-weight: bold;">₱{{ number_format($product->price, 2) }}</p>
        </div>
    </div>
</div> --}}


                        @endforeach
                    </div>
                @endif
               <table class='table'>
                    <tr>
                        <th>Order Number</th>
                        <td><b>{{ $item->order_number }}</b></td>
                    </tr>
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
                     <th>Staff</th>
                     <td>{{ $item->users->name ?? "no data" }} - {{ ucfirst($item->users->role) ?? "no data" }}</td>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
   document.addEventListener('DOMContentLoaded', () => {
       const selectedContainer = document.getElementById('selected-products');
       const productBoxes = document.querySelectorAll('.product-box');
       const totalPriceEl = document.getElementById('total-price');
       const orderForm = document.querySelector('form');

       // -- Click handler for product boxes --
       productBoxes.forEach(box => {
           box.addEventListener('click', () => {
               const productId = box.dataset.id;
               const productName = box.dataset.name;
               const productPrice = parseFloat(box.dataset.price);

               if (!selectedContainer || !productId || !productName || isNaN(productPrice)) return;

               if (selectedContainer.querySelector(`input[name="product_id[]"][value="${productId}"]`)) {
                //    alert(`${productName} is already added.`);

                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: `${productName} is already added.`
                    });

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

               selectedContainer.appendChild(group);
                box.classList.add('selected'); // <-- This line

                updateTotal();

                group.querySelector('.quantity-input')?.addEventListener('input', updateTotal);
                group.querySelector('.remove-item')?.addEventListener('click', () => {
                    group.remove();
                    box.classList.remove('selected'); // remove highlight when product is removed
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

            // 🧮 Handle discount
            const selectedDiscount = document.querySelector('input[name="discount"]:checked');
            const discountPercent = selectedDiscount ? parseFloat(selectedDiscount.value) : 0;

            const discountAmount = total * (discountPercent / 100);
            const discountedTotal = total - discountAmount;

            // 💡 Update the visible total
            if (totalPriceEl) {
                totalPriceEl.textContent = discountedTotal.toFixed(2);
            }

            // 🖨️ Log to console
            console.log("Original Total:", total.toFixed(2));
            console.log("Discount (" + discountPercent + "%):", discountAmount.toFixed(2));
            console.log("Discounted Total:", discountedTotal.toFixed(2));
        }

        // 🔁 Bind change event to discount radios so total updates when selected
        document.querySelectorAll('input[name="discount"]').forEach(radio => {
            radio.addEventListener('change', updateTotal);
        });


       // -- Form submit handler --
       if (orderForm) {
           orderForm.addEventListener('submit', function (e) {
               e.preventDefault();

               Swal.fire({
                    title: "Do you want to send these items to the kitchen? Is this your final order?",
                    showDenyButton: true,
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                    denyButtonText: `Don't send`
                    }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {

                        const items = [];
                        document.querySelectorAll('.selected-item').forEach(group => {
                            const productId = group.querySelector('input[name="product_id[]"]')?.value;
                            const quantity = parseInt(group.querySelector('input[name="quantity[]"]')?.value || '0');
                            const price = parseFloat(group.querySelector('.product-price')?.textContent.replace(/[₱,]/g, '') || '0');

                            if (productId && quantity > 0 && price > 0) {
                                items.push({
                                    product_id: productId,
                                    quantity,
                                    price,
                                    subtotal: price * quantity
                                });
                            }
                        });

                        const tableNumber = document.querySelector('input[name="table_number"]')?.value;
                        const orderId = document.querySelector('input[name="orders_id"]')?.value;
                        const ordersUsersId = document.querySelector('input[name="orders_users_id"]')?.value;

                        // 🧮 Get selected discount value
                        const selectedDiscount = document.querySelector('input[name="discount"]:checked');
                        const discountPercent = selectedDiscount ? parseFloat(selectedDiscount.value) : 0;

                        // 💰 Calculate total and discounted total
                        const total = items.reduce((acc, item) => acc + item.subtotal, 0);
                        const discountAmount = total * (discountPercent / 100);
                        const discountedTotal = total - discountAmount;

                        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                        if (!tableNumber || !orderId || !ordersUsersId || !csrf) {
                            alert("Missing form information.");
                            return;
                        }

                        $.post('/store-orderitems', {
                            tableNumber,
                            orderId,
                            ordersUsersId,
                            items,
                            total,                    // Original total
                            discount: discountPercent,
                            discounted_price: discountedTotal,
                            _token: csrf
                        }, function (res) {
                            Swal.fire("The order has been sent to the kitchen for preparation!", "", "success");
                            window.location.href = '/show-orders/' + orderId + '/' + res.order_type;
                        }).fail(err => {
                            // console.error('Submission failed:', err);

                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: `Please double check the data and/or the table number has already taken.`
                            });
                        });


                    } else if (result.isDenied) {
                        Swal.fire("No items were sent to the kitchen.", "", "info");
                    }
                });
           });
       }

       // -- Live Timer --
       const timerElement = document.getElementById('live-timer');
       if (timerElement) {
           const updatedAt = timerElement.getAttribute('data-updated');
           const startTime = new Date(updatedAt).getTime();

           const updateLiveTimer = () => {
               const now = Date.now();
               const diff = now - startTime;

               const h = Math.floor(diff / (1000 * 60 * 60));
               const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
               const s = Math.floor((diff % (1000 * 60)) / 1000);

               timerElement.textContent = `${h > 0 ? h + 'h ' : ''}${m.toString().padStart(2, '0')}m ${s.toString().padStart(2, '0')}s`;
           };

           updateLiveTimer();
           setInterval(updateLiveTimer, 1000);
       }

       // -- Static Duration Timer --
       const durationElement = document.getElementById('duration-timer');
       if (durationElement) {
           const startAttr = durationElement.getAttribute('data-start');
           const endAttr = durationElement.getAttribute('data-end');

           const start = new Date(startAttr).getTime();
           const end = new Date(endAttr).getTime();

           if (!isNaN(start) && !isNaN(end) && end > start) {
               const diff = end - start;
               const h = Math.floor(diff / (1000 * 60 * 60));
               const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
               const s = Math.floor((diff % (1000 * 60)) / 1000);

               durationElement.textContent = `${h > 0 ? h + 'h ' : ''}${m.toString().padStart(2, '0')}m ${s.toString().padStart(2, '0')}s`;
           }
       }


       // Highlight already selected product boxes and bind remove/quantity events
        document.querySelectorAll('#selected-products .selected-item').forEach(group => {
            const productId = group.getAttribute('data-id');
            const box = document.querySelector(`.product-box[data-id="${productId}"]`);
            if (box) {
                box.classList.add('selected');
            }

            // Quantity input
            group.querySelector('.quantity-input').addEventListener('input', updateTotal);

            // Remove button
            group.querySelector('.remove-item').addEventListener('click', () => {
                group.remove();
                if (box) box.classList.remove('selected');
                updateTotal();
            });
        });

updateTotal();

   });
</script>

<script>
    $(document).ready(function () {
        $(document).on("keyup", ".search-menu-input", function() {
            var value = $(this).val().toLowerCase();
            $("body > div.content > div > div.col-lg-7.col-md-7.col-sm-12 > div > div > div.table-responsive > div > div").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    })
</script>
@endsection
