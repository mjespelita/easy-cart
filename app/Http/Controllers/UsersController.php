<?php

namespace App\Http\Controllers;

use App\Models\{Logs, User};
use App\Http\Requests\StoreUsersRequest;
use App\Http\Requests\UpdateUsersRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('users.users', [
            'users' => User::orderBy('id', 'desc')->paginate(10)
        ]);
    }

    // // public function trash()
    // // {
    // //     return view('users.trash-users', [
    // //         'users' => User::where('isTrash', '1')->paginate(10)
    // //     ]);
    // // }

    public function restore($usersId)
    {
        /* Log ************************************************** */
        $oldName = User::where('id', $usersId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') restored a Users "'.$oldName.'".']);
        /******************************************************** */

        User::where('id', $usersId)->update(['isTrash' => '0']);

        return redirect('/users');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create-users');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUsersRequest $request)
    {
        User::create(['name' => $request->name,'email' => $request->email,'password' => $request->password,'role' => $request->role]);

        /* Log ************************************************** */
        // Logs::create(['log' => Auth::user()->name.' created a new Users '.'"'.$request->name.'"']);
        /******************************************************** */

        return back()->with('success', 'Users Added Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $users, $usersId)
    {
        return view('users.show-users', [
            'item' => User::where('id', $usersId)->first()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $users, $usersId)
    {
        return view('users.edit-users', [
            'item' => User::where('id', $usersId)->first()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUsersRequest $request, User $users, $usersId)
    {
        /* Log ************************************************** */
        // $oldName = User::where('id', $usersId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' updated a Users from "'.$oldName.'" to "'.$request->name.'".']);
        /******************************************************** */

        User::where('id', $usersId)->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role
        ]);

        return back()->with('success', 'Users Updated Successfully!');
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(User $users, $usersId)
    {
        return view('users.delete-users', [
            'item' => User::where('id', $usersId)->first()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $users, $usersId)
    {

        /* Log ************************************************** */
        // $oldName = User::where('id', $usersId)->value('name');
        // Logs::create(['log' => Auth::user()->name.' deleted a Users "'.$oldName.'".']);
        /******************************************************** */

        User::where('id', $usersId)->delete();

        return redirect('/users');
    }

    public function bulkDelete(Request $request) {

        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = User::where('id', $value)->value('name');
            // Logs::create(['log' => Auth::user()->name.' deleted a Users "'.$oldName.'".']);
            /******************************************************** */

            $deletable = User::find($value);
            $deletable->delete();
        }
        return response()->json("Deleted");
    }

    public function bulkMoveToTrash(Request $request) {

        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = User::where('id', $value)->value('name');
            // Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') deleted a Users "'.$oldName.'".']);
            /******************************************************** */

            $deletable = User::find($value);
            $deletable->update(['isTrash' => '1']);
        }
        return response()->json("Deleted");
    }

    public function bulkRestore(Request $request)
    {
        foreach ($request->ids as $value) {

            /* Log ************************************************** */
            $oldName = User::where('id', $value)->value('name');
            Logs::create(['log' => Auth::user()->name.' ('.Auth::user()->role.') restored a Users "'.$oldName.'".']);
            /******************************************************** */

            $restorable = User::find($value);
            $restorable->update(['isTrash' => '0']);
        }
        return response()->json("Restored");
    }
}
