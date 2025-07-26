<?php

namespace App\Http\Controllers;

use App\Models\{Logs, Types};
use App\Http\Requests\StoreTypesRequest;
use App\Http\Requests\UpdateTypesRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TypesController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('types.types', [
            'types' => Types::where('isTrash', '0')->paginate(10)
        ]);
    }

    public function trash()
    {
        return view('types.trash-types', [
            'types' => Types::where('isTrash', '1')->paginate(10)
        ]);
    }

    public function restore($typesId)
    {
        /* Log ************************************************** */
        $oldName = Types::where('id', $typesId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') restored a Types "'.$oldName.'".']);
        /******************************************************** */

        Types::where('id', $typesId)->update(['isTrash' => '0']);

        return redirect('/types');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('types.create-types');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTypesRequest $request)
    {
        Types::create(['name' => $request->name]);

        /* Log ************************************************** */
        // Logs::create(['log' => Auth::user()->name.' created a new Types '.'"'.$request->name.'"']);
        /******************************************************** */

        return back()->with('success', 'Types Added Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Types $types, $typesId)
    {
        return view('types.show-types', [
            'item' => Types::where('id', $typesId)->first()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Types $types, $typesId)
    {
        return view('types.edit-types', [
            'item' => Types::where('id', $typesId)->first()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTypesRequest $request, Types $types, $typesId)
    {
        /* Log ************************************************** */
        $oldName = Types::where('id', $typesId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' updated a Types from "'.$oldName.'" to "'.$request->name.'".']);
        /******************************************************** */

        Types::where('id', $typesId)->update(['name' => $request->name]);

        return back()->with('success', 'Types Updated Successfully!');
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(Types $types, $typesId)
    {
        return view('types.delete-types', [
            'item' => Types::where('id', $typesId)->first()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Types $types, $typesId)
    {

        /* Log ************************************************** */
        $oldName = Types::where('id', $typesId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' deleted a Types "'.$oldName.'".']);
        /******************************************************** */

        Types::where('id', $typesId)->update(['isTrash' => '1']);

        return redirect('/types');
    }

    public function bulkDelete(Request $request) {

        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = Types::where('id', $value)->value('name');
            // Logs::create(['log' => Auth::user()->name.' deleted a Types "'.$oldName.'".']);
            /******************************************************** */

            $deletable = Types::find($value);
            $deletable->delete();
        }
        return response()->json("Deleted");
    }

    public function bulkMoveToTrash(Request $request) {

        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = Types::where('id', $value)->value('name');
            // Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') deleted a Types "'.$oldName.'".']);
            /******************************************************** */

            $deletable = Types::find($value);
            $deletable->update(['isTrash' => '1']);
        }
        return response()->json("Deleted");
    }

    public function bulkRestore(Request $request)
    {
        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = Types::where('id', $value)->value('name');
            Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') restored a Types "'.$oldName.'".']);
            /******************************************************** */

            $restorable = Types::find($value);
            $restorable->update(['isTrash' => '0']);
        }
        return response()->json("Restored");
    }
}