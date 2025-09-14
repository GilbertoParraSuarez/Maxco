<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ZonesController extends Controller
{
    public function index()
    {
        // Solo zonas activas en el listado
        $zones = Zone::where('activo', true)
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);

        return Inertia::render('Zones/Index', ['zones' => $zones]);
    }

    public function create()
    {
        return Inertia::render('Zones/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_zona' => 'required|string|max:255|unique:zones,nombre_zona',
            'descripcion' => 'nullable|string|max:255',
        ]);

        Zone::create($data + ['activo' => true]);

        return redirect()->route('zones.index')->with('success','Zona creada correctamente');
    }

    public function edit(Zone $zone)
    {
        return Inertia::render('Zones/Edit', ['zone' => $zone]);
    }

    public function update(Request $request, Zone $zone)
    {
        $data = $request->validate([
            'nombre_zona' => 'required|string|max:255|unique:zones,nombre_zona,'.$zone->id_zona.',id_zona',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $zone->update($data);

        return redirect()->route('zones.index')->with('success','Zona actualizada correctamente');
    }

    public function destroy(Zone $zone)
    {
        $zone->delete();
        return redirect()->route('zones.index')->with('success','Zona eliminada');
    }

    /** Activar/Desactivar zona */
    public function toggle(Zone $zone)
    {
        $zone->activo = ! $zone->activo;
        $zone->save();

        return back()->with('success', $zone->activo ? 'Zona activada' : 'Zona desactivada');
    }
}
