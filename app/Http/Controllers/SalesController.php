<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Client;
use App\Models\Vendor;
use App\Models\Zone;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SalesController extends Controller
{
    /** Listado: solo ventas NO anuladas */
    public function index()
    {
        $sales = Sale::with([
                'customer:id_cliente,nombre,email',
                'vendor:id_vendedor,nombre',
                'zone:id_zona,nombre_zona'
            ])
            ->where('estado', '!=', 'ANULADA')
            ->latest('fecha')
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);

        return Inertia::render('Sales/Index', [
            'sales' => $sales
        ]);
    }

    /** Form crear: catálogos activos */
    public function create()
    {
        $customers = Client::where('activo', true)->orderBy('nombre')
            ->get(['id_cliente','nombre','email']);
        $vendors   = Vendor::where('activo', true)->orderBy('nombre')
            ->get(['id_vendedor','nombre']);
        $zones     = Zone::where('activo', true)->orderBy('nombre_zona')
            ->get(['id_zona','nombre_zona']);
        $products  = Product::where('activo', true)->orderBy('nombre')
            ->get(['id_producto','nombre','precio','stock','sku','categoria']);

        return Inertia::render('Sales/Create', [
            'customers' => $customers,
            'vendors'   => $vendors,
            'zones'     => $zones,
            'products'  => $products,
            'defaults'  => [
                'moneda' => 'USD',
                'tasa_cambio' => 1.0000,
                'impuesto_porcentaje' => 15,
            ],
        ]);
    }

    /** Guardar venta + detalles (sin enviar total_linea, lo calcula MySQL) */
        public function store(Request $request) 
    {
        try {
            $data = $request->validate([
                'id_cliente'   => 'required|exists:clients,id_cliente',
                'id_vendedor'  => 'required|exists:vendors,id_vendedor',
                'id_zona'      => 'required|exists:zones,id_zona',
                'numero_documento' => 'nullable|string|max:100',
                'metodo_pago'  => 'required|string|max:50',
                'observaciones'=> 'nullable|string|max:500',
                'detalles'     => 'required|array|min:1',
                'detalles.*.id_producto'    => 'required|exists:products,id_producto',
                'detalles.*.cantidad'       => 'required|numeric|min:1',
                'detalles.*.precio_unitario'=> 'required|numeric|min:0', // Agregado para validación
                'detalles.*.descuento'      => 'nullable|numeric|min:0',
                'detalles.*.impuesto'       => 'nullable|numeric|min:0',
                'detalles.*.unidad'         => 'nullable|string|max:20',
            ]);

            // Validar número de documento único por vendedor (si se proporciona)
            if (!empty($data['numero_documento'])) {
                $existingSale = Sale::where('numero_documento', $data['numero_documento'])
                    ->where('id_vendedor', $data['id_vendedor'])
                    ->first();
                
                if ($existingSale) {
                    return back()->withErrors([
                        'numero_documento' => 'Ya existe una venta con este número de documento para el vendedor seleccionado.'
                    ]);
                }
            }

            // Forzar USD & tasa 1
            $data['moneda'] = 'USD';
            $data['tasa_cambio'] = 1;

            $montoTotal = 0; 
            $impuestoTotal = 0; 
            $descuentoTotal = 0;

            DB::transaction(function () use ($data, &$montoTotal, &$impuestoTotal, &$descuentoTotal) {
                
                $detallesSanit = [];
                
                foreach ($data['detalles'] as $index => $line) {
                    try {
                        $product = Product::lockForUpdate()->findOrFail($line['id_producto']);
                        
                        // Stock
                        $qty = (int) $line['cantidad'];
                        if ($product->stock < $qty) {
                            throw ValidationException::withMessages([
                                'stock' => "Stock insuficiente para '{$product->nombre}'. Disponible: {$product->stock}, solicitado: {$qty}."
                            ]);
                        }

                        // Precio desde BD (no editable)
                        $price = (float) $product->precio;
                        
                        // Validar que el precio del frontend coincida (seguridad)
                        $frontendPrice = (float) ($line['precio_unitario'] ?? 0);
                        if (abs($price - $frontendPrice) > 0.01) {
                            throw ValidationException::withMessages([
                                'detalles' => "El precio del producto '{$product->nombre}' ha cambiado. Precio actual: $" . number_format($price, 2)
                            ]);
                        }

                        $desc = (float)($line['descuento'] ?? 0);
                        $subtotal = $qty * $price;
                        $descuento_val = min($desc, $subtotal);
                        $base_imponible = $subtotal - $descuento_val;

                        $imp_pct = isset($line['impuesto']) ? (float)$line['impuesto'] : 15.0;
                        
                        // Validar porcentaje de impuesto razonable
                        if ($imp_pct < 0 || $imp_pct > 100) {
                            throw ValidationException::withMessages([
                                'detalles' => "El porcentaje de impuesto debe estar entre 0% y 100% (línea " . ($index + 1) . ")."
                            ]);
                        }
                        
                        $impuesto_val = round($base_imponible * ($imp_pct / 100), 2);

                        $detallesSanit[] = [
                            'id_producto'     => $product->id_producto,
                            'cantidad'        => $qty,
                            'precio_unitario' => round($price, 2),
                            'subtotal'        => round($subtotal, 2),
                            'descuento'       => round($descuento_val, 2),
                            'impuesto'        => round($impuesto_val, 2),
                            'unidad'          => $line['unidad'] ?? 'UND',
                        ];

                        $montoTotal     += ($base_imponible + $impuesto_val);
                        $impuestoTotal  += $impuesto_val;
                        $descuentoTotal += $descuento_val;

                        // Descontar stock
                        $product->decrement('stock', $qty);
                        
                    } catch (\Exception $e) {
                        if ($e instanceof ValidationException) {
                            throw $e;
                        }
                        
                        throw ValidationException::withMessages([
                            'detalles' => "Error procesando el producto en la línea " . ($index + 1) . ": " . $e->getMessage()
                        ]);
                    }
                }

                // Generar número de documento si no se proporciona
                $numeroDocumento = $data['numero_documento'];
                if (empty($numeroDocumento)) {
                    do {
                        $numeroDocumento = 'VT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                        $exists = Sale::where('numero_documento', $numeroDocumento)
                            ->where('id_vendedor', $data['id_vendedor'])
                            ->exists();
                    } while ($exists);
                }

                $sale = Sale::create([
                    'id_cliente'     => $data['id_cliente'],
                    'id_vendedor'    => $data['id_vendedor'],
                    'id_zona'        => $data['id_zona'],
                    'fecha'          => now(),
                    'numero_documento' => $numeroDocumento,
                    'metodo_pago'    => $data['metodo_pago'],
                    'moneda'         => 'USD',
                    'tasa_cambio'    => 1,
                    'observaciones'  => $data['observaciones'] ?? null,
                    'impuesto_total' => round($impuestoTotal, 2),
                    'descuento_total'=> round($descuentoTotal, 2),
                    'monto_total'    => round($montoTotal, 2),
                    'estado'         => 'CONFIRMADA',
                ]);

                foreach ($detallesSanit as $det) {
                    $sale->details()->create($det);
                }
            });

            return redirect()->route('sales.index')->with('success', 'Venta registrada correctamente');

        } catch (UniqueConstraintViolationException $e) {
            // Manejar error de constraint de unicidad específicamente
            if (strpos($e->getMessage(), 'numero_documento') !== false) {
                return back()->withErrors([
                    'numero_documento' => 'El número de documento ya existe para este vendedor. Use un número diferente o déjelo vacío para generar uno automático.'
                ])->withInput();
            }
            
            return back()->withErrors([
                'message' => 'Error de datos duplicados. Verifique la información e intente nuevamente.'
            ])->withInput();
            
        } catch (ValidationException $e) {
            // Los errores de validación se manejan automáticamente por Laravel
            throw $e;
            
        } catch (\Exception $e) {
            // Log del error para debugging
            \Log::error('Error al crear venta: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Rollback manual si no estamos en transacción
            DB::rollBack();
            
            return back()->withErrors([
                'message' => 'Ocurrió un error interno al procesar la venta. Por favor, intente nuevamente o contacte al administrador.'
            ])->withInput();
        }
    }
    


    /** Mostrar cabecera + detalles */
    public function show(Sale $sale)
    {
        $sale->load([
            'customer:id_cliente,nombre,email',
            'vendor:id_vendedor,nombre',
            'zone:id_zona,nombre_zona',
            'details.product:id_producto,nombre,sku'
        ]);

        return Inertia::render('Sales/Show', ['sale' => $sale]);
    }

    /** (Opcional) Editar – solo algunos campos del header */
    public function edit(Sale $sale)
    {
        $sale->load(['details.product:id_producto,nombre,precio,sku']);
        $customers = Client::where('activo', true)->orderBy('nombre')->get(['id_cliente','nombre']);
        $vendors   = Vendor::where('activo', true)->orderBy('nombre')->get(['id_vendedor','nombre']);
        $zones     = Zone::where('activo', true)->orderBy('nombre_zona')->get(['id_zona','nombre_zona']);
        $products  = Product::where('activo', true)->orderBy('nombre')->get(['id_producto','nombre','precio','stock','sku']);

        return Inertia::render('Sales/Edit', compact('sale','customers','vendors','zones','products'));
    }

    /** (Opcional) Actualizar datos del header de la venta */
    public function update(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'numero_documento' => 'nullable|string|max:100',
            'metodo_pago'      => 'required|string|max:50',
            'moneda'           => 'required|string|max:10',
            'tasa_cambio'      => 'nullable|numeric|min:0',
            'observaciones'    => 'nullable|string|max:500',
        ]);

        $sale->update($data);

        return redirect()->route('sales.show', ['sale' => $sale->id_venta])
            ->with('success','Venta actualizada');
    }

    /** Anular venta */
    public function cancel(Sale $sale)
    {
        if ($sale->estado === 'ANULADA') {
            return back()->with('success', 'La venta ya estaba anulada');
        }
        $sale->estado = 'ANULADA';
        $sale->save();

        return back()->with('success', 'Venta anulada');
    }

    /** Eliminar (soft delete) */
    public function destroy(Sale $sale)
    {
        $sale->delete();
        return redirect()->route('sales.index')->with('success','Venta eliminada');
    }
}
