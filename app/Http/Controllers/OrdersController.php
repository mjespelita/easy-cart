<?php

namespace App\Http\Controllers;

use App\Models\{Logs, Orderitems, Orders};
use App\Http\Requests\StoreOrdersRequest;
use App\Http\Requests\UpdateOrdersRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('orders.orders', [
            'orders' => Orders::where('isTrash', '0')->orderBy('id', 'DESC')->paginate(12)
        ]);
    }

    public function preparing()
    {
        return view('orders.preparing-orders', [
            'orders' => Orders::where('isTrash', '0')->where('status', 'preparing')->orderBy('id', 'DESC')->paginate(12)
        ]);
    }

    public function done()
    {
        return view('orders.done-orders', [
            'orders' => Orders::where('isTrash', '0')->where('status', 'done')->orderBy('id', 'DESC')->paginate(12)
        ]);
    }

    public function trash()
    {
        return view('orders.trash-orders', [
            'orders' => Orders::where('isTrash', '1')->paginate(10)
        ]);
    }

    public function restore($ordersId)
    {
        /* Log ************************************************** */
        $oldName = Orders::where('id', $ordersId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') restored a Orders "'.$oldName.'".']);
        /******************************************************** */

        Orders::where('id', $ordersId)->update(['isTrash' => '0']);

        return redirect('/orders');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($typeId)
    {
        $newOrder = Orders::create([
            'status' => 'pending',
            'sent_to_kitchen_at' => '',
            'done_at' => '',
            'users_id' => Auth::user()->id,
            'order_number' => 'ORDER_#'.Orders::count() + 1,
            'order_type' => $typeId
        ]);
        return redirect('/show-orders/'.$newOrder->id.'/'.$typeId);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrdersRequest $request)
    {
        Orders::create(['status' => $request->status,'sent_to_kitchen_at' => $request->sent_to_kitchen_at,'done_at' => $request->done_at,'users_id' => $request->users_id]);

        /* Log ************************************************** */
        // Logs::create(['log' => Auth::user()->name.' created a new Orders '.'"'.$request->name.'"']);
        /******************************************************** */

        return back()->with('success', 'Orders Added Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Orders $orders, $ordersId, $typesId)
    {
        $orderItems = Orderitems::where('orders_id', $ordersId)->get();

        return view('orders.show-orders', [
            'item' => Orders::where('id', $ordersId)->first(),
            'orderItems' => $orderItems,
            'typesId' => $typesId
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Orders $orders, $ordersId, $typesId)
{
    // Reset the order (optional logic)
    Orders::where('id', $ordersId)->update([
        'sent_to_kitchen_at' => "",
        // 'table_number' => null,
        'total' => null,
        'status' => 'pending',
    ]);

    $order = Orders::findOrFail($ordersId);
    $orderItems = Orderitems::where('orders_id', $ordersId)->get();

    return view('orders.show-orders', [
        'item' => $order,
        'orderItems' => $orderItems,
        'typesId' => $typesId
    ]);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrdersRequest $request, Orders $orders, $ordersId)
    {
        /* Log ************************************************** */
        $oldName = Orders::where('id', $ordersId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' updated a Orders from "'.$oldName.'" to "'.$request->name.'".']);
        /******************************************************** */

        Orders::where('id', $ordersId)->update([
            'status' => $request->status,
            'sent_to_kitchen_at' => $request->sent_to_kitchen_at,
            'done_at' => $request->done_at,
            'users_id' => $request->users_id
        ]);

        return back()->with('success', 'Orders Updated Successfully!');
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(Orders $orders, $ordersId)
    {
        return view('orders.delete-orders', [
            'item' => Orders::where('id', $ordersId)->first()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Orders $orders, $ordersId)
    {



        /* Log ************************************************** */
        // $oldName = Orders::where('id', $ordersId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' deleted a Orders "'.$oldName.'".']);
        /******************************************************** */

        Orderitems::where('orders_id', $ordersId)->delete();

        // Orders::where('id', $ordersId)->update(['isTrash' => '1']);
        Orders::where('id', $ordersId)->delete();

        return redirect('/orders');
    }

    public function bulkDelete(Request $request) {

        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            // $oldName = Orders::where('id', $value)->value('name');
            // Logs::create(['log' => Auth::user()->name.' deleted a Orders "'.$oldName.'".']);
            /******************************************************** */

            $deletable = Orders::find($value);
            $deletable->delete();
        }
        return response()->json("Deleted");
    }

    public function bulkMoveToTrash(Request $request) {

        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = Orders::where('id', $value)->value('name');
            // Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') deleted a Orders "'.$oldName.'".']);
            /******************************************************** */

            $deletable = Orders::find($value);
            $deletable->update(['isTrash' => '1']);
        }
        return response()->json("Deleted");
    }

    public function bulkRestore(Request $request)
    {
        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = Orders::where('id', $value)->value('name');
            Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') restored a Orders "'.$oldName.'".']);
            /******************************************************** */

            $restorable = Orders::find($value);
            $restorable->update(['isTrash' => '0']);
        }
        return response()->json("Restored");
    }
}
