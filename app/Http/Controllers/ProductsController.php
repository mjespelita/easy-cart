<?php

namespace App\Http\Controllers;

use App\Models\{Logs, Products};
use App\Http\Requests\StoreProductsRequest;
use App\Http\Requests\UpdateProductsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductsController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('products.products', [
            'products' => Products::where('isTrash', '0')->paginate(10)
        ]);
    }

    public function trash()
    {
        return view('products.trash-products', [
            'products' => Products::where('isTrash', '1')->paginate(10)
        ]);
    }

    public function restore($productsId)
    {
        /* Log ************************************************** */
        $oldName = Products::where('id', $productsId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') restored a Products "'.$oldName.'".']);
        /******************************************************** */

        Products::where('id', $productsId)->update(['isTrash' => '0']);

        return redirect('/products');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create-products');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductsRequest $request)
    {
        Products::create([
            'product_id' => $request->product_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'types_id' => $request->types_id
        ]);

        /* Log ************************************************** */
        // Logs::create(['log' => Auth::user()->name.' created a new Products '.'"'.$request->name.'"']);
        /******************************************************** */

        return back()->with('success', 'Products Added Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Products $products, $productsId)
    {
        return view('products.show-products', [
            'item' => Products::where('id', $productsId)->first()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Products $products, $productsId)
    {
        return view('products.edit-products', [
            'item' => Products::where('id', $productsId)->first()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductsRequest $request, Products $products, $productsId)
    {
        /* Log ************************************************** */
        $oldName = Products::where('id', $productsId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' updated a Products from "'.$oldName.'" to "'.$request->name.'".']);
        /******************************************************** */

        Products::where('id', $productsId)->update([
            'product_id' => $request->product_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'types_id' => $request->types_id
        ]);

        return back()->with('success', 'Products Updated Successfully!');
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(Products $products, $productsId)
    {
        return view('products.delete-products', [
            'item' => Products::where('id', $productsId)->first()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Products $products, $productsId)
    {

        /* Log ************************************************** */
        $oldName = Products::where('id', $productsId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' deleted a Products "'.$oldName.'".']);
        /******************************************************** */

        Products::where('id', $productsId)->update(['isTrash' => '1']);

        return redirect('/products');
    }

    public function bulkDelete(Request $request) {

        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = Products::where('id', $value)->value('name');
            // Logs::create(['log' => Auth::user()->name.' deleted a Products "'.$oldName.'".']);
            /******************************************************** */

            $deletable = Products::find($value);
            $deletable->delete();
        }
        return response()->json("Deleted");
    }

    public function bulkMoveToTrash(Request $request) {

        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = Products::where('id', $value)->value('name');
            // Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') deleted a Products "'.$oldName.'".']);
            /******************************************************** */

            $deletable = Products::find($value);
            $deletable->update(['isTrash' => '1']);
        }
        return response()->json("Deleted");
    }

    public function bulkRestore(Request $request)
    {
        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = Products::where('id', $value)->value('name');
            Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') restored a Products "'.$oldName.'".']);
            /******************************************************** */

            $restorable = Products::find($value);
            $restorable->update(['isTrash' => '0']);
        }
        return response()->json("Restored");
    }
}
