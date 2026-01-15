<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\User_ln;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $search = $request->input('search');

        $viewId = $request->query('view');

        $users = User::paginate(10);
    
        $selectedUser = null;
    
        if ($viewId) {
            $selectedUser = User::with('members')->find($viewId);
        }

        $users = User::select(
                'hd_id',
                'status',
                'phone1',
                'email',
                'fname',
                'lname',
                'barangay',
                'city',
                'province'
            )
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('fname', 'LIKE', "%{$search}%")
                    ->orWhere('lname', 'LIKE', "%{$search}%")
                    ->orWhere('phone1', 'LIKE', "%{$search}%")
                    ->orWhereRaw("CONCAT(fname, ' ', lname) LIKE ?", ["%{$search}%"]);
                });
            })
            ->orderByDesc('hd_id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('User/Index', [
            'users' => $users,
            'perPage' => $perPage,
            'search' => $search,
            'selectedUser' => $selectedUser,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $encrypted_password = Hash::make($request['password']);
 
        User::create(
            [
                'fname' => $request['fname'],
                'lname' => $request['lname'],
                'email' => $request['email'],
                'password' => $encrypted_password
            ]
        );

        return redirect()->back();
    } 

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('members'); 

        return inertia('User/Index', [
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {   
        $data = $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable',
            'fname' => 'string',
            'lname' => 'string',
            'status' => 'nullable|integer',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
