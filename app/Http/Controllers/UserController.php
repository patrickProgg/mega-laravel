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
                'fname',
                'mname',
                'lname',
                'email',
                'birthday',
                'age',
                'phone1',
                'phone2',
                'purok',
                'barangay',
                'city',
                'province',
                'zip_code'
            )
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('fname', 'LIKE', "%{$search}%")
                    ->orWhere('lname', 'LIKE', "%{$search}%")
                    ->orWhere('phone1', 'LIKE', "%{$search}%")
                    ->orWhereRaw("CONCAT(fname, ' ', lname) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("CONCAT(barangay, ' ', city, ' ', province) LIKE ?", ["%{$search}%"]);
                });
            })
            ->orderByDesc('hd_id')
            ->paginate($perPage)
            ->withQueryString();;

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
        
        // Create the household head
        $householdHead = User::create(
            [
                'fname' => $request['fname'],
                'mname' => $request['mname'],
                'lname' => $request['lname'],
                'email' => $request['email'],
                'password' => $encrypted_password,
                'birthday' => $request['birthday'],
                'age' => $request['age'],
                'phone1' => $request['phone1'],
                'phone2' => $request['phone2'],
                'purok' => $request['purok'],
                'barangay' => $request['barangay'],
                'city' => $request['city'],
                'province' => $request['province'],
                'zip_code' => $request['zip_code'],
                'status' => $request['status'] ?? 0, // Default to active if not provided
            ]
        );
        
        // Store family members if any
        if ($request->has('family_members') && is_array($request->family_members)) {
            foreach ($request->family_members as $familyMember) {
                User_ln::create([
                    'hd_id' => $householdHead->hd_id, // Foreign key to user_hd table
                    'fname' => $familyMember['fname'] ?? null,
                    'mname' => $familyMember['mname'] ?? null,
                    'lname' => $familyMember['lname'] ?? null,
                    'birthday' => $familyMember['birthday'] ?? null,
                    'age' => $familyMember['age'] ?? null,
                    'phone1' => $familyMember['phone1'] ?? null,
                    'phone2' => $familyMember['phone2'] ?? null,
                    'relation' => $familyMember['relation'] ?? null,
                    'purok' => $familyMember['purok'] ?? null,
                    'barangay' => $familyMember['barangay'] ?? null,
                    'city' => $familyMember['city'] ?? null,
                    'province' => $familyMember['province'] ?? null,
                    'zip_code' => $familyMember['zip_code'] ?? null,
                    'status' => 0, // Default status
                ]);
            }
        }
        
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
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $user->update([
            'fname' => $request['fname'],
            'mname' => $request['mname'],
            'lname' => $request['lname'],
            'email' => $request['email'],
            'birthday' => $request['birthday'],
            'age' => $request['age'],
            'phone1' => $request['phone1'],
            'phone2' => $request['phone2'],
            'purok' => $request['purok'],
            'barangay' => $request['barangay'],
            'city' => $request['city'],
            'province' => $request['province'],
            'zip_code' => $request['zip_code'],
            'status' => $request['status'] ?? $user->status,
        ]);
        
        // // Update or add family members
        // if ($request->has('family_members') && is_array($request->family_members)) {
        //     // Option 1: Delete all existing and create new ones
        //     User_ln::where('hd_id', $id)->delete();
            
        //     foreach ($request->family_members as $familyMember) {
        //         User_ln::create([
        //             'hd_id' => $id,
        //             'fname' => $familyMember['fname'] ?? null,
        //             'mname' => $familyMember['mname'] ?? null,
        //             'lname' => $familyMember['lname'] ?? null,
        //             'birthday' => $familyMember['birthday'] ?? null,
        //             'age' => $familyMember['age'] ?? null,
        //             'phone1' => $familyMember['phone1'] ?? null,
        //             'phone2' => $familyMember['phone2'] ?? null,
        //             'relation' => $familyMember['relation'] ?? null,
        //             'status' => $familyMember['status'] ?? 0,
        //         ]);
        //     }
            
        //     // Option 2: Update existing and create new (if you have IDs)
        //     // This is more complex and requires sending member IDs from frontend
        // }
        
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Get all provinces
     */
    public function getProvinces()
    {
        $provinces = DB::table('address_provinces')
            ->select('id', 'name')
            ->distinct()
            ->orderBy('name')
            ->get();
        
        return response()->json($provinces);
    }

    /**
     * Get cities by province
     */
    public function getCities($province)
    {
        $cities = DB::table('address_cities')
            ->where('province_id', $province)
            ->select('id', 'name', 'zip_code')
            ->distinct()
            ->orderBy('name')
            ->get();
        
        return response()->json($cities);
    }

    /**
     * Get barangays by city
     */
    public function getBarangays($city)
    {
        $barangays = DB::table('address_barangays')
            ->where('city_id', $city)
            ->select('id', 'name')
            ->distinct()
            ->orderBy('name')
            ->get();
        
        return response()->json($barangays);
    }

    /**
     * Get puroks by barangay
     */
    public function getPuroks($barangay)
    {
        $puroks = DB::table('address_puroks')
            ->where('barangay_id', $barangay)
            ->select('id', 'name')
            ->distinct()
            ->orderBy('name')
            ->get();
        
        return response()->json($puroks);
    }
}
