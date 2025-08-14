
@extends('layouts.main')

@section('content')
    <div class='row'>
        <div class='col-lg-6 col-md-6 col-sm-12'>
            <h1>All Products</h1>
        </div>
        <div class='col-lg-6 col-md-6 col-sm-12' style='text-align: right;'>
            <a href='{{ url('trash-products') }}'><button class='btn btn-danger'><i class='fas fa-trash'></i> Trash <span class='text-warning'>{{ App\Models\Products::where('isTrash', '1')->count() }}</span></button></a>
            <a href='{{ route('products.create') }}'><button class='btn btn-success'><i class='fas fa-plus'></i> Add Products</button></a>
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
                            <form action='{{ url('/products-paginate') }}' method='get'>
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
                    <form action='{{ url('/products-filter') }}' method='get'>
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
                    <form action='{{ url('/products-search') }}' method='GET'>
                        <div class='input-group'>
                            <input type='text' name='search' value='{{ request()->get('search') }}' class='form-control' placeholder='Search...'>
                            <div class='input-group-append'>
                                <button class='btn btn-success' type='submit'><i class='fa fa-search'></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class='table-responsive'>
                <table class='table table-striped'>
                    <thead>
                        <tr>
                            <th scope='col'>
                            <input type='checkbox' name='' id='' class='checkAll'>
                            </th>
                            <th>Product ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($products as $item)
                            <tr>
                                <th scope='row'>
                                    <input type='checkbox' name='' id='' class='check' data-id='{{ $item->id }}'>
                                </th>
                                <td>{{ $item->product_id }}</td>
                                <td>{{ $item->name }}</td>
                                <td><b class="fw-bold text-success">{{ $item->types->name ?? "no data" }}</b></td>
                                <td>{{ $item->description }}</td>
                                <td>₱{{ Smark\Smark\Math::convertToMoneyFormat($item->price) }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="#" class="btn btn-secondary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#showProductConsumeHistory{{ $item->id }}">
                                            <i class="fas fa-bars"></i>
                                        </a>

                                        <!-- History Modal -->
                                        <div class="modal fade" id="showProductConsumeHistory{{ $item->id }}" tabindex="-1" aria-labelledby="showProductConsumeHistory{{ $item->id }}Label" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">

                                                <!-- Modal Header -->
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="showProductConsumeHistory{{ $item->id }}Label">{{ $item->name ?? "no data" }} Orders Per Day</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <!-- Modal Body -->
                                                <div class="modal-body">
                                                    <div style="height: 50vh; overflow-y: scroll;">
                                                        @php
                                                            // Get all orders for this product, grouped by date
                                                            $ordersByDate = App\Models\Orderitems::where('products_id', $item->id)
                                                                ->selectRaw('DATE(created_at) as date, SUM(quantity) as total_quantity')
                                                                ->groupBy('date')
                                                                ->orderBy('date', 'desc')
                                                                ->get();
                                                        @endphp

                                                        <ul>
                                                            @forelse ($ordersByDate as $order)
                                                                <li><b class="text-primary">{{ \Carbon\Carbon::parse($order->date)->format('F j, Y') }}</b> - <b class="text-success">({{ $order->total_quantity }})</b></li>
                                                            @empty
                                                                <li>No orders...</li>
                                                            @endforelse
                                                        </ul>
                                                    </div>
                                                </div>

                                                <!-- Modal Footer -->
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>

                                                </div>
                                            </div>
                                        </div>

                                        <a href="{{ route('products.show', $item->id) }}" class="btn btn-success btn-sm w-100">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $item->id) }}" class="btn btn-info btn-sm w-100">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('products.delete', $item->id) }}" class="btn btn-danger btn-sm w-100">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td>No Record...</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{ $products->links('pagination::bootstrap-5') }}

    <script src='{{ url('assets/jquery/jquery.min.js') }}'></script>
    <script>
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

                $.post('/products-delete-all-bulk-data', {
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

                $.post('/products-move-to-trash-all-bulk-data', {
                    ids: array,
                    _token: $("meta[name='csrf-token']").attr('content')
                }, function (res) {
                    window.location.reload();
                })
            })
        });
    </script>
@endsection
