<?php

namespace App\Http\Controllers;

use App\Models\Technician;
use Illuminate\Http\Request;

class TechnicianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $technicians = Technician::all();
        return view('technicians.index')
            ->with('technicians', $technicians);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $modalMode = "create";
        $technicians = Technician::all();
        return view('technicians.index')
            ->with('modalMode', $modalMode)
            ->with('technicians', $technicians);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $this->validate($request, ['isSenior'=>'required']);
        $technician = new Technician();
        $technician->isSenior = $request->isSenior;
        if(!empty($request->name_family)) $technician->name_family = $request->name_family;
        if(!empty($request->name_first)) $technician->name_first = $request->name_first;
        $technician->save();
        return redirect('technicians')->with('success','Technician created.');
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
        $modalMode = "edit";
        $technicians = Technician::all();
        $technician = Technician::find($id);
        return view('technicians.index')
            ->with('technician', $technician)
            ->with('modalMode', $modalMode)
            ->with('technicians', $technicians);
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
