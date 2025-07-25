
@extends('layouts.main')

@section('content')
    <!-- Main Content Area -->
    <h1>Welcome to the Dashboard</h1>
    <b>Hello, {{ Auth::user()->name }}</b>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Products</h5>
                    <h1>{{ App\Models\Products::count() }}</h1>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">All Orders</h5>
                    <h1>{{ App\Models\Orders::count() }}</h1>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Preparing Orders</h5>
                    <h1>{{ App\Models\Orders::where('status', 'preparing')->count() }}</h1>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Done Orders</h5>
                    <h1>{{ App\Models\Orders::where('status', 'done')->count() }}</h1>
                </div>
            </div>
        </div>
    </div>
@endsection
