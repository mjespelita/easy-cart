<?php

namespace App\Http\Controllers;

use App\Models\{Logs, Orderitems, Orders};
use App\Http\Requests\StoreOrderitemsRequest;
use App\Http\Requests\UpdateOrderitemsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderitemsController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('orderitems.orderitems', [
            'orderitems' => Orderitems::where('isTrash', '0')->paginate(10)
        ]);
    }

    public function trash()
    {
        return view('orderitems.trash-orderitems', [
            'orderitems' => Orderitems::where('isTrash', '1')->paginate(10)
        ]);
    }

    public function restore($orderitemsId)
    {
        /* Log ************************************************** */
        $oldName = Orderitems::where('id', $orderitemsId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') restored a Orderitems "'.$oldName.'".']);
        /******************************************************** */

        Orderitems::where('id', $orderitemsId)->update(['isTrash' => '0']);

        return redirect('/orderitems');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('orderitems.create-orderitems');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tableNumber' => 'required',
            'orderId' => 'required',
            'ordersUsersId' => 'required',
            'items' => 'required',
            'total' => 'required',
            'discount' => 'required',
            'discounted_price' => 'required',
        ]);

        Orderitems::where('orders_id', $request->orderId)->delete();

        // insert table number to orders table

        Orders::where('id', $request->orderId)->update([
            'table_number' => $request->tableNumber,
            'total' => $request->total,
            'sent_to_kitchen_at' => date('Y-m-d H:i:s'),
            'status' => 'preparing',
            'discount' => $request->discount,
            'discounted_price' => $request->discounted_price,
        ]);

        foreach ($request->items as $key => $value) {
            Orderitems::create([
                'orders_id' => $request->orderId,
                'orders_users_id' => $request->ordersUsersId,
                'products_id' => $value['product_id'],
                'quantity' => $value['quantity'],
                'total' => $value['price'],
                'subtotal' => $value['subtotal']
            ]);
        }

        /* Log ************************************************** */
        // Logs::create(['log' => Auth::user()->name.' created a new Orderitems '.'"'.$request->name.'"']);
        /******************************************************** */

        // return back()->with('success', 'Orderitems Added Successfully!');
        return response()->json([
            'order_type' => Orders::where('id', $request->orderId)->value('order_type')
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Orderitems $orderitems, $orderitemsId)
    {
        return view('orderitems.show-orderitems', [
            'item' => Orderitems::where('id', $orderitemsId)->first()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Orderitems $orderitems, $orderitemsId)
    {
        return view('orderitems.edit-orderitems', [
            'item' => Orderitems::where('id', $orderitemsId)->first()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderitemsRequest $request, Orderitems $orderitems, $orderitemsId)
    {
        /* Log ************************************************** */
        $oldName = Orderitems::where('id', $orderitemsId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' updated a Orderitems from "'.$oldName.'" to "'.$request->name.'".']);
        /******************************************************** */

        Orderitems::where('id', $orderitemsId)->update(['orders_id' => $request->orders_id,'orders_users_id' => $request->orders_users_id,'products_id' => $request->products_id,'quantity' => $request->quantity]);

        return back()->with('success', 'Orderitems Updated Successfully!');
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(Orderitems $orderitems, $orderitemsId)
    {
        return view('orderitems.delete-orderitems', [
            'item' => Orderitems::where('id', $orderitemsId)->first()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Orderitems $orderitems, $orderitemsId)
    {

        /* Log ************************************************** */
        $oldName = Orderitems::where('id', $orderitemsId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' deleted a Orderitems "'.$oldName.'".']);
        /******************************************************** */

        Orderitems::where('id', $orderitemsId)->update(['isTrash' => '1']);

        return redirect('/orderitems');
    }

    public function bulkDelete(Request $request) {

        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = Orderitems::where('id', $value)->value('name');
            // Logs::create(['log' => Auth::user()->name.' deleted a Orderitems "'.$oldName.'".']);
            /******************************************************** */

            $deletable = Orderitems::find($value);
            $deletable->delete();
        }
        return response()->json("Deleted");
    }

    public function bulkMoveToTrash(Request $request) {

        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = Orderitems::where('id', $value)->value('name');
            // Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') deleted a Orderitems "'.$oldName.'".']);
            /******************************************************** */

            $deletable = Orderitems::find($value);
            $deletable->update(['isTrash' => '1']);
        }
        return response()->json("Deleted");
    }

    public function bulkRestore(Request $request)
    {
        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = Orderitems::where('id', $value)->value('name');
            Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') restored a Orderitems "'.$oldName.'".']);
            /******************************************************** */

            $restorable = Orderitems::find($value);
            $restorable->update(['isTrash' => '0']);
        }
        return response()->json("Restored");
    }
}
