<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Model Viewer - Dark Theme</title>
    <link href='{{ url('assets/bootstrap/bootstrap.min.css') }}' rel='stylesheet'>
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            padding-top: 20px;
        }

        a {
            color: #00bfff;
        }

        a:hover {
            color: #33ccff;
        }

        .sidebar {
            min-height: 100vh;
            background-color: #1e1e1e;
            border-right: 1px solid #333;
            padding: 1rem;
        }

        .active-link {
            font-weight: bold;
            color: #00bfff !important;
        }

        .card {
            background-color: #1e1e1e;
            color: #e0e0e0;
            border: 1px solid #333;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #00bfff;
        }

        .card-footer {
            border-top: 1px solid #333;
        }

        .btn-primary {
            background-color: #00bfff;
            border-color: #00bfff;
        }

        .btn-primary:hover {
            background-color: #33ccff;
            border-color: #33ccff;
        }

        .list-group-item {
            background-color: #2a2a2a;
            color: #e0e0e0;
            font-size: 0.875rem;
            border-color: #333;
        }

        .table {
            color: #e0e0e0;
        }

        .table th, .table td {
            background-color: #1e1e1e;
            border-color: #333;
        }

        .alert-info {
            background-color: #2c3e50;
            color: #00bfff;
            border: none;
        }

        .pagination .page-link {
            background-color: #1e1e1e;
            color: #00bfff;
            border: 1px solid #333;
        }

        .pagination .page-link:hover {
            background-color: #333;
        }

        .pagination .active .page-link {
            background-color: #00bfff;
            color: #000;
            border-color: #00bfff;
        }

        body > div > div > div.col-md-10 > nav > div.d-none.flex-sm-fill.d-sm-flex.align-items-sm-center.justify-content-sm-between > div:nth-child(1) > p {
            color: aliceblue !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h5 class="text-light">Models</h5>
                <ul class="nav flex-column">
                    @foreach ($models as $m)
                        <li class="nav-item">
                            <a class="nav-link {{ $m == $model ? 'active-link' : '' }}" href="{{ route('models.index', $m) }}">
                                {{ $m }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <h3 class="mt-3 text-info">{{ $model ?? 'Select a Model' }}</h3>

                @if ($records->count())
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    @foreach ($records->first()->getAttributes() as $key => $value)
                                        <th>{{ $key }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($records as $record)
                                    <tr>
                                        @foreach ($record->getAttributes() as $value)
                                            <td>{{ \Str::limit($value, 50) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $records->links('pagination::bootstrap-5') }}
                @elseif ($model)
                    <div class="alert alert-info">No records found for <strong>{{ $model }}</strong>.</div>
                @else
                    <div class="row">
                        @foreach ($models as $m)
                            @php
                                $modelClass = "App\\Models\\$m";
                                $instance = class_exists($modelClass) ? $modelClass::first() : null;
                                $columns = $instance ? array_keys($instance->getAttributes()) : [];
                            @endphp

                            <div class="mb-4 col-md-4">
                                <div class="shadow-sm card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $m }}</h5>

                                        @if ($columns)
                                            <ul class="list-group list-group-flush">
                                                @foreach ($columns as $col)
                                                    <li class="list-group-item">{{ $col }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="mt-2 text-muted">No records found (or table is empty)</div>
                                        @endif
                                    </div>
                                    <div class="bg-transparent card-footer">
                                        <a href="{{ route('models.index', $m) }}" class="btn btn-sm btn-primary">View Records</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
