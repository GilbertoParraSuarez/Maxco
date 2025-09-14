<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Inertia\Inertia;
use Illuminate\Http\Request;

class VendorsController extends Controller
{
    public function index()
    {
        $vendors = Vendor::latest()->paginate(10);
        return Inertia::render('Vendors/Index', [
            'vendors' => $vendors
        ]);
    }

    public function create()
    {
        return Inertia::render('Vendors/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|unique:vendors,email',
            'telefono' => 'nullable|string|max:50',
        ]);

        Vendor::create($data);

        return redirect()->route('vendors.index')->with('success','Vendedor creado correctamente');
    }

    public function edit(Vendor $vendor)
    {
        return Inertia::render('Vendors/Edit', [
            'vendor' => $vendor
        ]);
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|unique:vendors,email,'.$vendor->id_vendedor.',id_vendedor',
            'telefono' => 'nullable|string|max:50',
        ]);

        $vendor->update($data);

        return redirect()->route('vendors.index')->with('success','Vendedor actualizado correctamente');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('vendors.index')->with('success','Vendedor eliminado');
    }
}
