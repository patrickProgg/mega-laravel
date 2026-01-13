<?php

namespace App\Http\Controllers;

use App\Models\Deceased;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MortController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $search = $request->input('search');

        $deceased = Deceased::join('user_hd', 'deceased.hd_id', '=', 'user_hd.hd_id')
            ->select(
                'deceased.*',    
                DB::raw("CONCAT(user_hd.fname, ' ', user_hd.lname) AS full_name"),
                DB::raw("CONCAT(user_hd.barangay, ' ', user_hd.city, ' ', user_hd.province) AS address"),
                'user_hd.email',
                'user_hd.phone1',
                'user_hd.email',
                'user_hd.email',
            )
        ->when($search, function ($q) use ($search) {
            $q->where(function ($q) use ($search) {
                $q->where(DB::raw("CONCAT(user_hd.fname, ' ', user_hd.lname)"), 'LIKE', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(user_hd.barangay, ' ', user_hd.city, ' ', user_hd.province)"), 'LIKE', "%{$search}%")
                    ->orWhere('deceased.dd_date_died', 'LIKE', "%{$search}%")
                    ->orWhere('user_hd.phone1', 'LIKE', "%{$search}%");
            });
        })
        ->where('user_hd.status', 2)
        ->orderBy('deceased.hd_id', 'desc')
        ->paginate($perPage)
        ->withQueryString();

        return Inertia::render('Mortuary/Index', [
            'deceased' => $deceased,
            'perPage' => $perPage,
            'search' => $search
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
