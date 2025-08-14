
@extends('layouts.main')

@section('content')
    <div class='row'>
        <div class='col-lg-6 col-md-6 col-sm-12'>
            <h1>Preparing Orders</h1>
        </div>
        <div class='col-lg-6 col-md-6 col-sm-12' style='text-align: right;'>
            {{-- <a href='{{ url('trash-orders') }}'><button class='btn btn-danger'><i class='fas fa-trash'></i> Trash <span class='text-warning'>{{ App\Models\Orders::where('isTrash', '1')->count() }}</span></button></a> --}}
            {{-- <a href='{{ route('orders.create') }}'><button class='btn btn-success'><i class='fas fa-plus'></i> Add Orders</button></a> --}}
        </div>
    </div>

    <div class='card'>
        <div class='card-body'>
            <div class='row'>
                <div class='mt-2 col-lg-4 col-md-4 col-sm-12'>
                    <div class='row'>
                        <div class='col-4'>
                            <button type='button' class='btn btn-outline-secondary dropdown-toggle' data-bs-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                                Action
                            </button>
                            <div class='dropdown-menu'>
                                <a class='dropdown-item bulk-move-to-trash' href='#'>
                                    <i class='fa fa-trash'></i> Move to Trash
                                </a>
                                <a class='dropdown-item bulk-delete' href='#'>
                                    <i class='fa fa-trash'></i> <span class='text-danger'>Delete Permanently</span> <br> <small>(this action cannot be undone)</small>
                                </a>
                            </div>
                        </div>
                        <div class='col-8'>
                            <form action='{{ url('/orders-paginate') }}' method='get'>
                                <div class='input-group'>
                                    <input type='number' name='paginate' class='form-control' placeholder='Paginate' value='{{ request()->get('paginate', 10) }}'>
                                    <div class='input-group-append'>
                                        <button class='btn btn-success' type='submit'><i class='fa fa-bars'></i></button>
                                    </div>
                                </div>
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
                <div class='mt-2 col-lg-4 col-md-4 col-sm-12'>
                    <form action='{{ url('/orders-filter') }}' method='get'>
                        <div class='input-group'>
                            <input type='date' class='form-control' id='from' name='from' required>
                            <b class='pt-2'>- to -</b>
                            <input type='date' class='form-control' id='to' name='to' required>
                            <div class='input-group-append'>
                                <button type='submit' class='btn btn-primary form-control'><i class='fas fa-filter'></i></button>
                            </div>
                        </div>
                        @csrf
                    </form>
                </div>
                <div class='mt-2 col-lg-4 col-md-4 col-sm-12'>
                    <!-- Search Form -->
                    <form action='{{ url('/orders-search') }}' method='GET'>
                        <div class='input-group'>
                            <input type='text' name='search' value='{{ request()->get('search') }}' class='form-control' placeholder='Search...'>
                            <div class='input-group-append'>
                                <button class='btn btn-success' type='submit'><i class='fa fa-search'></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-3 row">
                @forelse($orders as $item)
                    <div class="mb-4 col-md-6 col-lg-4">
                        <div class="shadow-sm card border-dark">
                            <div class="text-white card-header d-flex justify-content-between align-items-center" style="background: #1B1B1B;">
                                <strong>Table: {{ $item->table_number }} from {{ $item->users->name ?? "no data" }} - {{ ucfirst($item->users->role) ?? "no data" }}</strong>
                                <input type="checkbox" class="form-check-input check" data-id="{{ $item->id }}">
                            </div>
                            <div class="card-body">

                                {{-- Timers --}}
                                @if ($item->status === 'preparing')
                                    <div class="mb-2">
                                        <strong>Preparing Time:</strong>
                                        <div class="timer-box" id="live-timer-{{ $item->id }}"
                                            data-updated="{{ $item->sent_to_kitchen_at }}"
                                            style="
                                                background-color: #000000;
                                                color: #C49C74;
                                                font-family: 'Courier New', monospace;
                                                font-size: 18px;
                                                padding: 8px 14px;
                                                border-radius: 6px;
                                                display: inline-block;
                                                min-width: 120px;
                                                text-align: center;
                                                box-shadow: 0 0 8px #C49C74;
                                            ">
                                        </div>
                                    </div>
                                @elseif ($item->status === 'done')
                                    <div class="mb-2">
                                        <strong>Duration:</strong>
                                        <div class="timer-box" id="duration-timer-{{ $item->id }}"
                                            data-start="{{ $item->sent_to_kitchen_at }}"
                                            data-end="{{ $item->done_at }}"
                                            style="
                                                background-color: #000000;
                                                color: #C49C74;
                                                font-family: 'Courier New', monospace;
                                                font-size: 18px;
                                                padding: 8px 14px;
                                                border-radius: 6px;
                                                display: inline-block;
                                                min-width: 120px;
                                                text-align: center;
                                                box-shadow: 0 0 8px #C49C74;
                                            ">
                                        </div>
                                    </div>
                                @endif

                                <p><strong>Items:</strong> {{ App\Models\Orderitems::where('orders_id', $item->id)->count() }}</p>

                                {{-- order page - order details list --}}

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" border="1" cellpadding="5" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Sub Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse (App\Models\Orderitems::where('orders_id', $item->id)->get() as $orderItem)
                                                <tr>
                                                    <td>{{ $orderItem->products->name ?? "No Data" }}</td>
                                                    <td>{{ $orderItem->quantity }}</td>
                                                    <td>₱ {{ number_format($orderItem->total, 2) }}</td>
                                                    <td>₱ {{ number_format($orderItem->subtotal, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4">No Items...</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- end order page - order details list --}}
                                <p><strong>Status:</strong>
                                    <span class="badge bg-{{ $item->status === 'done' ? 'success' : ($item->status === 'preparing' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </p>

                                <p><strong>Sent to Kitchen At:</strong><br>
                                    {{ !empty($item->sent_to_kitchen_at) ? Smark\Smark\Dater::humanReadableDateWithDayAndTime($item->sent_to_kitchen_at) : '---' }}
                                </p>
                                <p><strong>Done At:</strong><br>
                                    {{ !empty($item->done_at) ? Smark\Smark\Dater::humanReadableDateWithDayAndTime($item->done_at) : '---' }}
                                </p>
                                <p><strong>Ordered By:</strong> {{ $item->users->name ?? 'No Data' }} - {{ ucfirst($item->users->role) ?? "no data" }}</p>
                                <p><strong>Order Number:</strong> <b class="text-primary">{{ $item->order_number }}</b></p>
                                <p><strong>Order Type:</strong> <b class="text-primary">{{ $item->types->name ?? "no data" }}</b></p>
                            </div>
                            <div class="card-footer d-flex gap-2">
                                <a href="{{ route('orders.show', ['ordersId' => $item->id, 'typeId' => $item->order_type]) }}"
                                class="btn btn-success btn-md flex-fill py-2" title="View Order">
                                    <i class="fas fa-eye"></i> View
                                </a>

                                @if ($item->status != 'done')
                                    <a href="{{ route('orders.edit', ['ordersId' => $item->id, 'typeId' => $item->order_type]) }}"
                                    class="btn btn-info btn-md flex-fill py-2" title="Edit Order">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endif

                                <a href="{{ route('orders.delete', $item->id) }}"
                                class="btn btn-danger btn-md flex-fill py-2"
                                title="Delete Order"
                                onclick="return confirm('Are you sure you want to delete this order?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center alert alert-warning">No Orders Found.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{ $orders->links('pagination::bootstrap-5') }}

    <script src='{{ url('assets/jquery/jquery.min.js') }}'></script>
    <script>

        function initTimers() {
        // Live timers (for preparing)
        document.querySelectorAll('[id^="live-timer-"]').forEach(el => {
            const start = new Date(el.dataset.updated).getTime();
            const interval = setInterval(() => {
                const now = new Date().getTime();
                const diff = now - start;
                const h = Math.floor(diff / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                el.textContent = `${h > 0 ? h + 'h ' : ''}${m.toString().padStart(2, '0')}m ${s.toString().padStart(2, '0')}s`;
            }, 1000);
        });

        // Static timers (for done)
        document.querySelectorAll('[id^="duration-timer-"]').forEach(el => {
            const start = new Date(el.dataset.start).getTime();
            const end = new Date(el.dataset.end).getTime();
            const diff = end - start;
            const h = Math.floor(diff / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            el.textContent = `${h > 0 ? h + 'h ' : ''}${m.toString().padStart(2, '0')}m ${s.toString().padStart(2, '0')}s`;
        });
    }

    document.addEventListener('DOMContentLoaded', initTimers);

        $(document).ready(function () {

            // checkbox

            var click = false;
            $('.checkAll').on('click', function() {
                $('.check').prop('checked', !click);
                click = !click;
                this.innerHTML = click ? 'Deselect' : 'Select';
            });

            $('.bulk-delete').click(function () {
                let array = [];
                $('.check:checked').each(function() {
                    array.push($(this).attr('data-id'));
                });

                $.post('/orders-delete-all-bulk-data', {
                    ids: array,
                    _token: $("meta[name='csrf-token']").attr('content')
                }, function (res) {
                    window.location.reload();
                })
            })

            $('.bulk-move-to-trash').click(function () {
                let array = [];
                $('.check:checked').each(function() {
                    array.push($(this).attr('data-id'));
                });

                $.post('/orders-move-to-trash-all-bulk-data', {
                    ids: array,
                    _token: $("meta[name='csrf-token']").attr('content')
                }, function (res) {
                    window.location.reload();
                })
            })
        });
    </script>
@endsection
