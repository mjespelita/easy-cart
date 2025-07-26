
@extends('layouts.main')

@section('content')
    <h1>Create a new products</h1>

    <div class='card'>
        <div class='card-body'>
            <form action='{{ route('products.store') }}' method='POST'>
                @csrf

        @php
            use App\Models\Products;

            $lastProduct = Products::orderByDesc('id')->first();
            $lastId = $lastProduct ? $lastProduct->product_id : 'MENU_0000';

            preg_match('/MENU_(\d+)/', $lastId, $matches);
            $nextNumber = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
            $nextProductId = 'MENU_' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        @endphp

        <div class='form-group'>
            {{-- <label for='product_id'>Product ID</label> --}}
            <input type='text' class='form-control' id='product_id' name='product_id' required value="{{ $nextProductId }}" readonly hidden>
        </div>

        <div class='form-group'>
            <label for='name'>Menu Name</label>
            <input type='text' class='form-control' id='name' name='name' required>
        </div>

        <div class='form-group'>
            <label for='name'>Menu Type</label>
            <select name="types_id" class="form-control" required>
                <option value="" disabled selected>Select type</option>
                @forelse (App\Models\Types::all() as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @empty
                    <option value="" disabled>No available types</option>
                @endforelse
            </select>
        </div>


        <div class='form-group'>
            <label for='name'>Description</label>
            <textarea name="description" id="" cols="30" class="form-control" rows="10" placeholder="Type here..." required></textarea>
        </div>

        <!-- Real-time price preview -->
        <div class="p-4 form-group">
            <h1 id="formatted-price" style="font-weight: bold; font-size: 1.2rem;">₱0.00</h1>
        </div>

        <!-- Price input -->
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" step="any" class="form-control" id="price" name="price" required>
        </div>

                <button type='submit' class='mt-3 btn btn-primary'>Create</button>
            </form>
        </div>
    </div>

    <!-- jQuery Script (make sure jQuery is loaded) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- jQuery logic -->
    <script>
        function formatMoney(value) {
            const number = parseFloat(value);
            if (isNaN(number)) return '₱0.00';
            return '₱' + number.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        $(document).ready(function () {
            $('#price').on('input', function () {
                const rawValue = $(this).val();
                $('#formatted-price').text(formatMoney(rawValue));
            });
        });
    </script>

@endsection
