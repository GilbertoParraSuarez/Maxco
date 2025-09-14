<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Client;
use App\Models\Vendor;
use App\Models\Zone;
use App\Models\Product;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['client','vendor','zone'])->latest()->paginate(10);
        return Inertia::render('Sales/Index', ['sales' => $sales]);
    }

    public function create()
    {
        return Inertia::render('Sales/Create', [
            'clients' => Client::all(),
            'vendors' => Vendor::all(),
            'zones' => Zone::all(),
            'products' => Product::all(),
        ]);
    }

    public function store(Request $request)
    {
        // Validaciones y lógica para guardar cabecera + detalles de la venta
    }

    public function edit(Sale $sale)
    {
        return Inertia::render('Sales/Edit', [
            'sale' => $sale->load(['details.product','client','vendor','zone']),
            'clients' => Client::all(),
            'vendors' => Vendor::all(),
            'zones' => Zone::all(),
            'products' => Product::all(),
        ]);
    }

    public function update(Request $request, Sale $sale)
    {
        // Validaciones y lógica para actualizar
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();
        return redirect()->route('sales.index')->with('success','Venta eliminada');
    }
}
