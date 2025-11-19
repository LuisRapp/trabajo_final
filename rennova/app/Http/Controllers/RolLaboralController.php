<?php

namespace App\Http\Controllers;

use App\Models\RolLaboral;
use Illuminate\Http\Request;

class RolLaboralController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('roles-laborales.index');
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
    public function show(RolLaboral $rolLaboral)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RolLaboral $rolLaboral)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RolLaboral $rolLaboral)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RolLaboral $rolLaboral)
    {
        //
    }
}
