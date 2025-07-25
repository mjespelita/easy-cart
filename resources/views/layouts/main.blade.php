
<!DOCTYPE html>
<html lang='{{ str_replace('_', '-', app()->getLocale()) }}'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <meta name='csrf-token' content='{{ csrf_token() }}'>
        <meta name='author' content='Mark Jason Penote Espelita'>
        <meta name='keywords' content='keyword1, keyword2'>
        <meta name='description' content='Dolorem natus ab illum beatae error voluptatem incidunt quis. Cupiditate ullam doloremque delectus culpa. Autem harum dolorem praesentium dolorum necessitatibus iure quo. Et ea aut voluptatem expedita.'>

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link href='{{ url('assets/bootstrap/bootstrap.min.css') }}' rel='stylesheet'>
        <!-- FontAwesome for icons -->
        <link href='{{ url('assets/font-awesome/css/all.min.css') }}' rel='stylesheet'>
        <link rel='stylesheet' href='{{ url('assets/custom/style.css') }}'>
        <link rel='icon' href='{{ url('assets/easy-cart-logo.jpg') }}'>
    </head>
    <body class='font-sans antialiased'>

        <!-- Sidebar for Desktop View -->
        <div class='sidebar' id='mobileSidebar'>
            <div class='logo'>
                <img src='{{ url('assets/easy-cart-logo.jpg') }}' alt='' width='50%'>
            </div>
            <a href="{{ url('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>

            {{-- <a href="{{ url('logs') }}" class="{{ request()->is('logs', 'create-logs', 'show-logs/*', 'edit-logs/*', 'delete-logs/*', 'logs-search*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i> Logs
            </a> --}}

            <a href="{{ url('orders') }}" class="{{ request()->is('orders', 'create-orders', 'trash-orders', 'show-orders/*', 'edit-orders/*', 'delete-orders/*', 'orders-search*') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i> <span class="text-danger fw-bold">{{ App\Models\Orders::count() }}</span> All Orders
            </a>

            <a href="{{ url('preparing-orders') }}" class="{{ request()->is('preparing-orders', 'create-preparing-orders', 'trash-preparing-orders', 'show-preparing-orders/*', 'edit-preparing-orders/*', 'delete-preparing-orders/*', 'preparing-orders-search*') ? 'active' : '' }}">
                <i class="fas fa-recycle"></i> <span class="text-danger fw-bold">{{ App\Models\Orders::where('status', 'preparing')->count() }}</span> Preparing Orders
            </a>

            <a href="{{ url('done-orders') }}" class="{{ request()->is('done-orders', 'create-done-orders', 'trash-done-orders', 'show-done-orders/*', 'edit-done-orders/*', 'delete-done-orders/*', 'done-orders-search*') ? 'active' : '' }}">
                <i class="fas fa-check"></i> <span class="text-danger fw-bold">{{ App\Models\Orders::where('status', 'done')->count() }}</span> Done Orders
            </a>

            <a href="{{ url('products') }}" class="{{ request()->is('products', 'create-products', 'trash-products', 'show-products/*', 'edit-products/*', 'delete-products/*', 'products-search*') ? 'active' : '' }}">
                <i class="fas fa-box-open"></i> Products
            </a>

            <a target="_blank" href="{{ url('kitchen.html') }}" class="">
                <i class="fas fa-utensils"></i> Kitchen Orders Board
            </a>

            {{-- <a href="{{ url('orderstatuslogs') }}" class="{{ request()->is('orderstatuslogs', 'create-orderstatuslogs', 'trash-orderstatuslogs', 'show-orderstatuslogs/*', 'edit-orderstatuslogs/*', 'delete-orderstatuslogs/*', 'orderstatuslogs-search*') ? 'active' : '' }}">
                <i class="fas fa-stream"></i> Order Status
            </a> --}}

            @if (Auth::user()->role === 'admin')
                <a href="{{ url('users') }}" class="{{ request()->is('users', 'create-users', 'trash-users', 'show-users/*', 'edit-users/*', 'delete-users/*', 'discounts-search*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> User Accounts
                </a>
                <a href="{{ url('discounts') }}" class="{{ request()->is('discounts', 'create-discounts', 'trash-discounts', 'show-discounts/*', 'edit-discounts/*', 'delete-discounts/*', 'discounts-search*') ? 'active' : '' }}">
                    <i class="fas fa-percentage"></i> Discount List
                </a>
            @endif

            <a href="/release-notes.html" class="">
                <i class="fas fa-earth"></i> Release Notes
            </a>

            <a href="{{ url('user/profile') }}">
                <i class="fas fa-user"></i> {{ Auth::user()->name }}
            </a>

        </div>

        <!-- Top Navbar -->
        <nav class='navbar navbar-expand-lg navbar-dark'>
            <div class='container-fluid'>
                <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav'
                    aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation' onclick='toggleSidebar()'>
                    <i class='fas fa-bars'></i>
                </button>
            </div>
        </nav>

        <x-main-notification />

        <div class='content'>
            @yield('content')
        </div>

        <!-- Bootstrap JS and dependencies -->
        <script src='{{ url('assets/bootstrap/bootstrap.bundle.min.js') }}'></script>

        <!-- Custom JavaScript -->
        <script src="{{ url('assets/custom/script.js') }}"></script>
        <script>
            function toggleSidebar() {
                document.getElementById('mobileSidebar').classList.toggle('active');
                document.getElementById('sidebar').classList.toggle('active');
            }
        </script>
    </body>
</html>
