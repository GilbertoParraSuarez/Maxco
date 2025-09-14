<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $paginator = Client::query()
            ->where('activo', 1)               // solo activos
            ->latest('id_cliente')
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);

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
            'nombre'         => ['required','string','max:255'],
            'email'          => ['nullable','email', Rule::unique('clients','email')->whereNull('deleted_at')],
            'telefono'       => ['nullable','string','max:50'],
            'direccion'      => ['nullable','string','max:255'],
            'identificacion' => ['nullable','string','max:50'],
        ]);

        $data['activo'] = 1;

        Client::create($data);

        return redirect()->route('customers.index')->with('success','Cliente creado correctamente');
    }

    public function edit(Client $customer)
    {
        return Inertia::render('Customers/Edit', ['customer' => $customer]);
    }

    public function update(Request $request, Client $customer)
    {
        $data = $request->validate([
            'nombre'         => ['required','string','max:255'],
            'email'          => ['nullable','email', Rule::unique('clients','email')
                                    ->ignore($customer->id_cliente,'id_cliente')
                                    ->whereNull('deleted_at')],
            'telefono'       => ['nullable','string','max:50'],
            'direccion'      => ['nullable','string','max:255'],
            'identificacion' => ['nullable','string','max:50'],
        ]);

        $customer->update($data);

        return redirect()->route('customers.index')->with('success','Cliente actualizado correctamente');
    }

    // Desactivar (no se muestra más en el listado)
    public function destroy(Client $customer)
    {
        $customer->update(['activo' => 0]);
        return redirect()->route('customers.index')->with('success','Cliente desactivado');
    }

    // Alternar estado (activar/desactivar)
    public function toggle(Client $customer)
    {
        $customer->update(['activo' => $customer->activo ? 0 : 1]);
        return back()->with('success', $customer->activo ? 'Cliente desactivado' : 'Cliente activado');
    }
}
