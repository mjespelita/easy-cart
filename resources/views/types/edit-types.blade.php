
@extends('layouts.main')

@section('content')
    <h1>Edit Types</h1>

    <div class='card'>
        <div class='card-body'>
            <form action='{{ route('types.update', $item->id) }}' method='POST'>
                @csrf
                
        <div class='form-group'>
            <label for='name'>Name</label>
            <input type='text' class='form-control' id='name' name='name' value='{{ $item->name }}' required>
        </div>
    
                <button type='submit' class='btn btn-primary mt-3'>Update</button>
            </form>
        </div>
    </div>

@endsection
