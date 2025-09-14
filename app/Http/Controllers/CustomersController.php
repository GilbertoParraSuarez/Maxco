<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Inertia\Inertia;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        // Pagina y conserva query string. onEachSide para mejores links numerados.
        $paginator = Client::query()
            ->latest('id_cliente') // asegúrate del campo correcto para ordenar
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);

        // Pasamos un shape CONSISTENTE para el front: data, links y meta
        $arr = $paginator->toArray();

        $customers = [
            'data'  => $arr['data'] ?? [],
            'links' => $arr['links'] ?? [],
            'meta'  => [
                'from'         => $arr['from'] ?? 0,
                'to'           => $arr['to'] ?? 0,
                'total'        => $arr['total'] ?? 0,
                'current_page' => $arr['current_page'] ?? 1,
                'last_page'    => $arr['last_page'] ?? 1,
                'per_page'     => (int)($arr['per_page'] ?? 10),
            ],
        ];

        return Inertia::render('Customers/Index', compact('customers'));
    }

    public function create()
    {
        return Inertia::render('Customers/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'nullable|email|unique:clients,email',
            'telefono' => 'nullable|string|max:50',
            'direccion'=> 'nullable|string|max:255',
        ]);

        Client::create($data);

        return redirect()->route('customers.index')->with('success','Cliente creado correctamente');
    }

    public function edit(Client $customer)
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer
        ]);
    }

    public function update(Request $request, Client $customer)
    {
        $data = $request->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'nullable|email|unique:clients,email,'.$customer->id_cliente.',id_cliente',
            'telefono' => 'nullable|string|max:50',
            'direccion'=> 'nullable|string|max:255',
        ]);

        $customer->update($data);

        return redirect()->route('customers.index')->with('success','Cliente actualizado correctamente');
    }

    public function destroy(Client $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success','Cliente eliminado');
    }
}
