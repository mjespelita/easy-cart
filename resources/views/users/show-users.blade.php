
@extends('layouts.main')

@section('content')
    <h1>Users Details</h1>

    <div class='card'>
        <div class='card-body'>
            <div class='table-responsive'>
                <table class='table'>

        <tr>
            <th>Name</th>
            <td>{{ $item->name }}</td>
        </tr>

        <tr>
            <th>Email</th>
            <td>{{ $item->email }}</td>
        </tr>

        <tr>
            <th>Role</th>
            <td>{{ $item->role }}</td>
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

    <a href='{{ route('users.index') }}' class='btn btn-primary'>Back to List</a>
@endsection
