<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorsController extends Controller
{
    public function index()
    {
        $vendors = Vendor::query()
            ->where('activo', 1)               // solo activos
            ->latest('id_vendedor')
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);

        return Inertia::render('Vendors/Index', [
            'vendors' => $vendors,
        ]);
    }

    public function create()
    {
        return Inertia::render('Vendors/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'         => ['required','string','max:255'],
            'email'          => ['nullable','email', Rule::unique('vendors','email')->whereNull('deleted_at')],
            'telefono'       => ['nullable','string','max:50'],
            'direccion'      => ['nullable','string','max:255'],
            'identificacion' => ['nullable','string','max:50'],
        ]);

        $data['activo'] = 1;

        Vendor::create($data);

        return redirect()->route('vendors.index')->with('success','Proveedor creado correctamente');
    }

    public function edit(Vendor $vendor)
    {
        return Inertia::render('Vendors/Edit', ['vendor' => $vendor]);
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'nombre'         => ['required','string','max:255'],
            'email'          => ['nullable','email', Rule::unique('vendors','email')
                                    ->ignore($vendor->id_vendedor,'id_vendedor')
                                    ->whereNull('deleted_at')],
            'telefono'       => ['nullable','string','max:50'],
            'direccion'      => ['nullable','string','max:255'],
            'identificacion' => ['nullable','string','max:50'],
        ]);

        $vendor->update($data);

        return redirect()->route('vendors.index')->with('success','Proveedor actualizado correctamente');
    }

    // Desactivar (no se muestra más en el listado)
    public function destroy(Vendor $vendor)
    {
        $vendor->update(['activo' => 0]);
        return redirect()->route('vendors.index')->with('success','Proveedor desactivado');
    }

    // Alternar estado (activar/desactivar)
    public function toggle(Vendor $vendor)
    {
        $vendor->update(['activo' => $vendor->activo ? 0 : 1]);
        return back()->with('success', $vendor->activo ? 'Proveedor desactivado' : 'Proveedor activado');
    }
}
