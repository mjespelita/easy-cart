
@extends('layouts.main')

@section('content')
    <h1>Create a new types</h1>

    <div class='card'>
        <div class='card-body'>
            <form action='{{ route('types.store') }}' method='POST'>
                @csrf
                
        <div class='form-group'>
            <label for='name'>Name</label>
            <input type='text' class='form-control' id='name' name='name' required>
        </div>
    
                <button type='submit' class='btn btn-primary mt-3'>Create</button>
            </form>
        </div>
    </div>

@endsection
