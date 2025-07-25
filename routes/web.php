
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

// end of import

use App\Http\Controllers\LogsController;
use App\Models\Logs;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

// end of import

use App\Http\Controllers\OrdersController;
use App\Models\Orders;

// end of import

use App\Http\Controllers\ProductsController;
use App\Models\Products;

// end of import

use App\Http\Controllers\OrderitemsController;
use App\Models\Orderitems;

// end of import

use App\Http\Controllers\OrderstatuslogsController;
use App\Models\Orderstatuslogs;

// end of import

use App\Http\Controllers\DiscountsController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CounterMiddleware;
use App\Models\Discounts;
use Smark\Smark\Math;
use Smark\Smark\PDFer;
use Smark\Smark\Dater;

// end of import

use App\Http\Controllers\UsersController;
use App\Models\Users;

// end of import




Route::get('/', function () {
    return redirect('/dashboard');
});

// API

Route::get('/orders-api', function () {

    $orders = Orders::where('status', 'preparing')->get();

    $ordersWithItems = $orders->map(function ($order) {
        $order->items = Orderitems::with(['orderUsers', 'products'])
            ->where('orders_id', $order->id)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'total' => Math::convertToMoneyFormat($item->total),
                    'subtotal' => Math::convertToMoneyFormat($item->subtotal),
                    'user_name' => $item->orderUsers->name ?? null,
                    'product_name' => $item->products->name ?? null,
                    'product_id' => $item->products->product_id ?? null,
                ];
            });

        return $order;
    });

    return response()->json($ordersWithItems);
});

Route::get('/mark-as-done-api/{ordersId}', function ($ordersId) {
    Orders::where('id', $ordersId)->update([
        'status' => 'done',
        'done_at' => date('Y-m-d H:i:s'),
    ]);

    return response()->json('done');
});


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/_database/{model?}', function ($model = null) {
        // Get all model class names in app/Models directory
        $modelPath = app_path('Models');
        $files = File::files($modelPath);

        $models = collect($files)->map(function ($file) {
            return $file->getFilenameWithoutExtension();
        });

        $records = collect();
        $perPage = 20;

        if ($model && class_exists("App\\Models\\$model")) {
            $modelClass = "App\\Models\\$model";
            $records = $modelClass::paginate($perPage);
        }

        return view('models.index', compact('models', 'records', 'model'));
    })->name('models.index');


    Route::get('/export-order-range', function (Request $request) {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $startDate = $request->start . ' 00:00:00';
        $endDate = $request->end . ' 23:59:59';

        // Filter done orders between the date range
        $orders = Orders::where('status', 'done')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Map each order and include order items with user and product details
        $ordersWithItems = $orders->map(function ($order) {
            $order->items = Orderitems::with(['orderUsers', 'products'])
                ->where('orders_id', $order->id)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'total' => Math::convertToMoneyFormat($item->total),
                        'subtotal' => Math::convertToMoneyFormat($item->subtotal),
                        'user_name' => $item->orderUsers->name ?? null,
                        'product_name' => $item->products->name ?? null,
                    ];
                });

            return $order;
        });

        // return response()->json($ordersWithItems);

        return PDFer::exportOrdersByDateRange($ordersWithItems, Dater::humanReadableDateWithDayAndTime($startDate), Dater::humanReadableDateWithDayAndTime($endDate));
        // return response()->json($ordersWithItems);
    });


    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard')->middleware(AuthMiddleware::class);

    Route::get('/admin-dashboard', function () {
        return view('admin-dashboard');
    })->middleware(AdminMiddleware::class);

    Route::get('/counter-staff-dashboard', function () {
        return view('counter-staff-dashboard');
    })->middleware(CounterMiddleware::class);

    // backup

    // Route::get('/backups', function () {
    //     // Path to the backups folder
    //     $backupFolder = public_path('backup'); // Adjust the path as needed
    //     $files = File::allFiles($backupFolder);

    //     return view('backups', compact('files')); // needs backup view
    // });

    Route::get('/backup-process', function () {
        // Call the backup artisan command
        Artisan::call('backup');

        // Optional: show the output in the browser
        $output = Artisan::output();

        // Return to a view or just show confirmation
        return redirect('/backups')->with('success', '✅ Backup completed.')->with('output', $output);
    });

    // end...

    Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');
    Route::get('/create-logs', [LogsController::class, 'create'])->name('logs.create');
    Route::get('/edit-logs/{logsId}', [LogsController::class, 'edit'])->name('logs.edit');
    Route::get('/show-logs/{logsId}', [LogsController::class, 'show'])->name('logs.show');
    Route::get('/delete-logs/{logsId}', [LogsController::class, 'delete'])->name('logs.delete');
    Route::get('/destroy-logs/{logsId}', [LogsController::class, 'destroy'])->name('logs.destroy');
    Route::post('/store-logs', [LogsController::class, 'store'])->name('logs.store');
    Route::post('/update-logs/{logsId}', [LogsController::class, 'update'])->name('logs.update');
    Route::post('/delete-all-bulk-data', [LogsController::class, 'bulkDelete']);

    // Logs Search
    Route::get('/logs-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $logs = Logs::when($search, function ($query) use ($search) {
            return $query->where('log', 'like', "%$search%");
        })->paginate(10);

        return view('logs.logs', compact('logs', 'search'));
    });

    // Logs Paginate
    Route::get('/logs-paginate', function (Request $request) {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided

        // Paginate the logs based on the 'paginate' value
        $logs = Logs::paginate($paginate); // Paginate with the specified number of items per page

        // Return the view with the paginated logs
        return view('logs.logs', compact('logs'));
    });

    // Logs Filter
    Route::get('/logs-filter', function (Request $request) {
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Default query for logs
        $query = Logs::query();

        // Convert dates to Carbon instances for better comparison
        $fromDate = $from ? Carbon::parse($from) : null;
        $toDate = $to ? Carbon::parse($to) : null;

        // Check if both 'from' and 'to' dates are provided
        if ($from && $to) {
            // If 'from' and 'to' are the same day (today)
            if ($fromDate->isToday() && $toDate->isToday()) {
                // Return results from today and include the 'from' date's data
                $logs = $query->whereDate('created_at', '=', Carbon::today())
                               ->orderBy('created_at', 'desc')
                               ->paginate(10);
            } else {
                // If 'from' date is greater than 'to' date, order ascending (from 'to' to 'from')
                if ($fromDate->gt($toDate)) {
                    $logs = $query->whereBetween('created_at', [$toDate, $fromDate])
                                   ->orderBy('created_at', 'asc')  // Ascending order
                                   ->paginate(10);
                } else {
                    // Otherwise, order descending (from 'from' to 'to')
                    $logs = $query->whereBetween('created_at', [$fromDate, $toDate])
                                   ->orderBy('created_at', 'desc')  // Descending order
                                   ->paginate(10);
                }
            }
        } else {
            // If 'from' or 'to' are missing, show all logs without filtering
            $logs = $query->paginate(10);  // Paginate results
        }

        // Return the view with logs and the selected date range
        return view('logs.logs', compact('logs', 'from', 'to'));
    });

    // end...

    Route::get('/mark-as-done-order/{ordersId}', function ($ordersId) {
        Orders::where('id', $ordersId)->update([
            'status' => 'done',
            'done_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect('/show-orders/'.$ordersId);
    });

    Route::get('/orders', [OrdersController::class, 'index'])->name('orders.index');
    Route::get('/preparing-orders', [OrdersController::class, 'preparing']);
    Route::get('/done-orders', [OrdersController::class, 'done']);

    Route::get('/create-orders', [OrdersController::class, 'create'])->name('orders.create');
    Route::get('/edit-orders/{ordersId}', [OrdersController::class, 'edit'])->name('orders.edit');
    Route::get('/show-orders/{ordersId}', [OrdersController::class, 'show'])->name('orders.show');
    Route::get('/delete-orders/{ordersId}', [OrdersController::class, 'delete'])->name('orders.delete');
    Route::get('/destroy-orders/{ordersId}', [OrdersController::class, 'destroy'])->name('orders.destroy');
    Route::post('/store-orders', [OrdersController::class, 'store'])->name('orders.store');
    Route::post('/update-orders/{ordersId}', [OrdersController::class, 'update'])->name('orders.update');
    Route::post('/orders-delete-all-bulk-data', [OrdersController::class, 'bulkDelete']);
    Route::post('/orders-move-to-trash-all-bulk-data', [OrdersController::class, 'bulkMoveToTrash']);
    Route::post('/orders-restore-all-bulk-data', [OrdersController::class, 'bulkRestore']);
    Route::get('/trash-orders', [OrdersController::class, 'trash']);
    Route::get('/restore-orders/{ordersId}', [OrdersController::class, 'restore'])->name('orders.restore');

    // Orders Search
    Route::get('/orders-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $orders = Orders::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%$search%");
        })->paginate(10);

        return view('orders.orders', compact('orders', 'search'));
    });

    // Orders Paginate
    Route::get('/orders-paginate', function (Request $request) {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided

        // Paginate the orders based on the 'paginate' value
        $orders = Orders::paginate($paginate); // Paginate with the specified number of items per page

        // Return the view with the paginated orders
        return view('orders.orders', compact('orders'));
    });

    // Orders Filter
    Route::get('/orders-filter', function (Request $request) {
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Default query for orders
        $query = Orders::query();

        // Convert dates to Carbon instances for better comparison
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;

        // Check if both 'from' and 'to' dates are provided
        if ($fromDate && $toDate) {
            // Ensure correct date filtering with full day range
            $orders = $query->whereBetween('created_at', [$fromDate, $toDate])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        } else {
            // If 'from' or 'to' are missing, show all orders without filtering
            $orders = $query->paginate(10);
        }

        // Return the view with orders and the selected date range
        return view('orders.orders', compact('orders', 'from', 'to'));
    });

    // end...

    Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
    Route::get('/create-products', [ProductsController::class, 'create'])->name('products.create');
    Route::get('/edit-products/{productsId}', [ProductsController::class, 'edit'])->name('products.edit');
    Route::get('/show-products/{productsId}', [ProductsController::class, 'show'])->name('products.show');
    Route::get('/delete-products/{productsId}', [ProductsController::class, 'delete'])->name('products.delete');
    Route::get('/destroy-products/{productsId}', [ProductsController::class, 'destroy'])->name('products.destroy');
    Route::post('/store-products', [ProductsController::class, 'store'])->name('products.store');
    Route::post('/update-products/{productsId}', [ProductsController::class, 'update'])->name('products.update');
    Route::post('/products-delete-all-bulk-data', [ProductsController::class, 'bulkDelete']);
    Route::post('/products-move-to-trash-all-bulk-data', [ProductsController::class, 'bulkMoveToTrash']);
    Route::post('/products-restore-all-bulk-data', [ProductsController::class, 'bulkRestore']);
    Route::get('/trash-products', [ProductsController::class, 'trash']);
    Route::get('/restore-products/{productsId}', [ProductsController::class, 'restore'])->name('products.restore');

    // Products Search
    Route::get('/products-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $products = Products::when($search, function ($query) use ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('product_id', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%");
            });
        })->paginate(10);

        return view('products.products', compact('products', 'search'));
    });

    // Products Paginate
    Route::get('/products-paginate', function (Request $request) {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided

        // Paginate the products based on the 'paginate' value
        $products = Products::paginate($paginate); // Paginate with the specified number of items per page

        // Return the view with the paginated products
        return view('products.products', compact('products'));
    });

    // Products Filter
    Route::get('/products-filter', function (Request $request) {
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Default query for products
        $query = Products::query();

        // Convert dates to Carbon instances for better comparison
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;

        // Check if both 'from' and 'to' dates are provided
        if ($fromDate && $toDate) {
            // Ensure correct date filtering with full day range
            $products = $query->whereBetween('created_at', [$fromDate, $toDate])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        } else {
            // If 'from' or 'to' are missing, show all products without filtering
            $products = $query->paginate(10);
        }

        // Return the view with products and the selected date range
        return view('products.products', compact('products', 'from', 'to'));
    });

    // end...

    Route::get('/orderitems', [OrderitemsController::class, 'index'])->name('orderitems.index');
    Route::get('/create-orderitems', [OrderitemsController::class, 'create'])->name('orderitems.create');
    Route::get('/edit-orderitems/{orderitemsId}', [OrderitemsController::class, 'edit'])->name('orderitems.edit');
    Route::get('/show-orderitems/{orderitemsId}', [OrderitemsController::class, 'show'])->name('orderitems.show');
    Route::get('/delete-orderitems/{orderitemsId}', [OrderitemsController::class, 'delete'])->name('orderitems.delete');
    Route::get('/destroy-orderitems/{orderitemsId}', [OrderitemsController::class, 'destroy'])->name('orderitems.destroy');
    Route::post('/store-orderitems', [OrderitemsController::class, 'store'])->name('orderitems.store');
    Route::post('/update-orderitems/{orderitemsId}', [OrderitemsController::class, 'update'])->name('orderitems.update');
    Route::post('/orderitems-delete-all-bulk-data', [OrderitemsController::class, 'bulkDelete']);
    Route::post('/orderitems-move-to-trash-all-bulk-data', [OrderitemsController::class, 'bulkMoveToTrash']);
    Route::post('/orderitems-restore-all-bulk-data', [OrderitemsController::class, 'bulkRestore']);
    Route::get('/trash-orderitems', [OrderitemsController::class, 'trash']);
    Route::get('/restore-orderitems/{orderitemsId}', [OrderitemsController::class, 'restore'])->name('orderitems.restore');

    // Orderitems Search
    Route::get('/orderitems-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $orderitems = Orderitems::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%$search%");
        })->paginate(10);

        return view('orderitems.orderitems', compact('orderitems', 'search'));
    });

    // Orderitems Paginate
    Route::get('/orderitems-paginate', function (Request $request) {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided

        // Paginate the orderitems based on the 'paginate' value
        $orderitems = Orderitems::paginate($paginate); // Paginate with the specified number of items per page

        // Return the view with the paginated orderitems
        return view('orderitems.orderitems', compact('orderitems'));
    });

    // Orderitems Filter
    Route::get('/orderitems-filter', function (Request $request) {
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Default query for orderitems
        $query = Orderitems::query();

        // Convert dates to Carbon instances for better comparison
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;

        // Check if both 'from' and 'to' dates are provided
        if ($fromDate && $toDate) {
            // Ensure correct date filtering with full day range
            $orderitems = $query->whereBetween('created_at', [$fromDate, $toDate])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        } else {
            // If 'from' or 'to' are missing, show all orderitems without filtering
            $orderitems = $query->paginate(10);
        }

        // Return the view with orderitems and the selected date range
        return view('orderitems.orderitems', compact('orderitems', 'from', 'to'));
    });

    // end...

    Route::get('/orderstatuslogs', [OrderstatuslogsController::class, 'index'])->name('orderstatuslogs.index');
    Route::get('/create-orderstatuslogs', [OrderstatuslogsController::class, 'create'])->name('orderstatuslogs.create');
    Route::get('/edit-orderstatuslogs/{orderstatuslogsId}', [OrderstatuslogsController::class, 'edit'])->name('orderstatuslogs.edit');
    Route::get('/show-orderstatuslogs/{orderstatuslogsId}', [OrderstatuslogsController::class, 'show'])->name('orderstatuslogs.show');
    Route::get('/delete-orderstatuslogs/{orderstatuslogsId}', [OrderstatuslogsController::class, 'delete'])->name('orderstatuslogs.delete');
    Route::get('/destroy-orderstatuslogs/{orderstatuslogsId}', [OrderstatuslogsController::class, 'destroy'])->name('orderstatuslogs.destroy');
    Route::post('/store-orderstatuslogs', [OrderstatuslogsController::class, 'store'])->name('orderstatuslogs.store');
    Route::post('/update-orderstatuslogs/{orderstatuslogsId}', [OrderstatuslogsController::class, 'update'])->name('orderstatuslogs.update');
    Route::post('/orderstatuslogs-delete-all-bulk-data', [OrderstatuslogsController::class, 'bulkDelete']);
    Route::post('/orderstatuslogs-move-to-trash-all-bulk-data', [OrderstatuslogsController::class, 'bulkMoveToTrash']);
    Route::post('/orderstatuslogs-restore-all-bulk-data', [OrderstatuslogsController::class, 'bulkRestore']);
    Route::get('/trash-orderstatuslogs', [OrderstatuslogsController::class, 'trash']);
    Route::get('/restore-orderstatuslogs/{orderstatuslogsId}', [OrderstatuslogsController::class, 'restore'])->name('orderstatuslogs.restore');

    // Orderstatuslogs Search
    Route::get('/orderstatuslogs-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $orderstatuslogs = Orderstatuslogs::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%$search%");
        })->paginate(10);

        return view('orderstatuslogs.orderstatuslogs', compact('orderstatuslogs', 'search'));
    });

    // Orderstatuslogs Paginate
    Route::get('/orderstatuslogs-paginate', function (Request $request) {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided

        // Paginate the orderstatuslogs based on the 'paginate' value
        $orderstatuslogs = Orderstatuslogs::paginate($paginate); // Paginate with the specified number of items per page

        // Return the view with the paginated orderstatuslogs
        return view('orderstatuslogs.orderstatuslogs', compact('orderstatuslogs'));
    });

    // Orderstatuslogs Filter
    Route::get('/orderstatuslogs-filter', function (Request $request) {
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Default query for orderstatuslogs
        $query = Orderstatuslogs::query();

        // Convert dates to Carbon instances for better comparison
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;

        // Check if both 'from' and 'to' dates are provided
        if ($fromDate && $toDate) {
            // Ensure correct date filtering with full day range
            $orderstatuslogs = $query->whereBetween('created_at', [$fromDate, $toDate])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        } else {
            // If 'from' or 'to' are missing, show all orderstatuslogs without filtering
            $orderstatuslogs = $query->paginate(10);
        }

        // Return the view with orderstatuslogs and the selected date range
        return view('orderstatuslogs.orderstatuslogs', compact('orderstatuslogs', 'from', 'to'));
    });

    // end...

    Route::get('/discounts', [DiscountsController::class, 'index'])->name('discounts.index');
    Route::get('/create-discounts', [DiscountsController::class, 'create'])->name('discounts.create');
    Route::get('/edit-discounts/{discountsId}', [DiscountsController::class, 'edit'])->name('discounts.edit');
    Route::get('/show-discounts/{discountsId}', [DiscountsController::class, 'show'])->name('discounts.show');
    Route::get('/delete-discounts/{discountsId}', [DiscountsController::class, 'delete'])->name('discounts.delete');
    Route::get('/destroy-discounts/{discountsId}', [DiscountsController::class, 'destroy'])->name('discounts.destroy');
    Route::post('/store-discounts', [DiscountsController::class, 'store'])->name('discounts.store');
    Route::post('/update-discounts/{discountsId}', [DiscountsController::class, 'update'])->name('discounts.update');
    Route::post('/discounts-delete-all-bulk-data', [DiscountsController::class, 'bulkDelete']);
    Route::post('/discounts-move-to-trash-all-bulk-data', [DiscountsController::class, 'bulkMoveToTrash']);
    Route::post('/discounts-restore-all-bulk-data', [DiscountsController::class, 'bulkRestore']);
    Route::get('/trash-discounts', [DiscountsController::class, 'trash']);
    Route::get('/restore-discounts/{discountsId}', [DiscountsController::class, 'restore'])->name('discounts.restore');

    // Discounts Search
    Route::get('/discounts-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $discounts = Discounts::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%$search%");
        })->paginate(10);

        return view('discounts.discounts', compact('discounts', 'search'));
    });

    // Discounts Paginate
    Route::get('/discounts-paginate', function (Request $request) {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided

        // Paginate the discounts based on the 'paginate' value
        $discounts = Discounts::paginate($paginate); // Paginate with the specified number of items per page

        // Return the view with the paginated discounts
        return view('discounts.discounts', compact('discounts'));
    });

    // Discounts Filter
    Route::get('/discounts-filter', function (Request $request) {
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Default query for discounts
        $query = Discounts::query();

        // Convert dates to Carbon instances for better comparison
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;

        // Check if both 'from' and 'to' dates are provided
        if ($fromDate && $toDate) {
            // Ensure correct date filtering with full day range
            $discounts = $query->whereBetween('created_at', [$fromDate, $toDate])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        } else {
            // If 'from' or 'to' are missing, show all discounts without filtering
            $discounts = $query->paginate(10);
        }

        // Return the view with discounts and the selected date range
        return view('discounts.discounts', compact('discounts', 'from', 'to'));
    });

    // end...

    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('/create-users', [UsersController::class, 'create'])->name('users.create');
    Route::get('/edit-users/{usersId}', [UsersController::class, 'edit'])->name('users.edit');
    Route::get('/show-users/{usersId}', [UsersController::class, 'show'])->name('users.show');
    Route::get('/delete-users/{usersId}', [UsersController::class, 'delete'])->name('users.delete');
    Route::get('/destroy-users/{usersId}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::post('/store-users', [UsersController::class, 'store'])->name('users.store');
    Route::post('/update-users/{usersId}', [UsersController::class, 'update'])->name('users.update');
    Route::post('/users-delete-all-bulk-data', [UsersController::class, 'bulkDelete']);
    Route::post('/users-move-to-trash-all-bulk-data', [UsersController::class, 'bulkMoveToTrash']);
    Route::post('/users-restore-all-bulk-data', [UsersController::class, 'bulkRestore']);
    Route::get('/trash-users', [UsersController::class, 'trash']);
    Route::get('/restore-users/{usersId}', [UsersController::class, 'restore'])->name('users.restore');

    // Users Search
    Route::get('/users-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $users = Users::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%$search%");
        })->paginate(10);

        return view('users.users', compact('users', 'search'));
    });

    // Users Paginate
    Route::get('/users-paginate', function (Request $request) {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided
    
        // Paginate the users based on the 'paginate' value
        $users = Users::paginate($paginate); // Paginate with the specified number of items per page
    
        // Return the view with the paginated users
        return view('users.users', compact('users'));
    });

    // Users Filter
    Route::get('/users-filter', function (Request $request) {
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');
    
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');
    
        // Default query for users
        $query = Users::query();
    
        // Convert dates to Carbon instances for better comparison
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;
    
        // Check if both 'from' and 'to' dates are provided
        if ($fromDate && $toDate) {
            // Ensure correct date filtering with full day range
            $users = $query->whereBetween('created_at', [$fromDate, $toDate])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        } else {
            // If 'from' or 'to' are missing, show all users without filtering
            $users = $query->paginate(10);
        }
    
        // Return the view with users and the selected date range
        return view('users.users', compact('users', 'from', 'to'));
    });

    // end...

});
