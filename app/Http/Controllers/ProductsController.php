<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index()
    {
        // Solo productos activos en el listado
        $products = Product::where('activo', true)
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);

        return Inertia::render('Products/Index', ['products' => $products]);
    }

    public function create()
    {
        return Inertia::render('Products/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio'      => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'categoria'   => 'nullable|string|max:100',
            'sku'         => 'nullable|string|max:100|unique:products,sku',
        ]);

        Product::create($data + ['activo' => true]);

        return redirect()
            ->route('products.index')
            ->with('success','Producto creado correctamente');
    }

    public function edit(Product $product)
    {
        return Inertia::render('Products/Edit', ['product' => $product]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio'      => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'categoria'   => 'nullable|string|max:100',
            'sku'         => 'nullable|string|max:100|unique:products,sku,'.$product->id_producto.',id_producto',
        ]);

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success','Producto actualizado correctamente');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success','Producto eliminado');
    }

    /** Activar/Desactivar producto */
    public function toggle(Product $product)
    {
        $product->activo = !$product->activo;
        $product->save();

        // Si lo desactivas, ya no saldrá en el index (que solo lista activos)
        return back()->with('success', $product->activo ? 'Producto activado' : 'Producto desactivado');
    }
}
