<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $desde = $request->input('desde'); // opcional
        $hasta = $request->input('hasta'); // opcional

        // 1A) todas las zonas por vendedor
        $zonasPorVendedor = DB::table('sales as s')
          ->join('vendors as v','v.id_vendedor','=','s.id_vendedor')
          ->join('zones   as z','z.id_zona','=','s.id_zona')
          ->where('s.estado','<>','ANULADA')
          ->groupBy('v.id_vendedor','v.nombre','z.id_zona','z.nombre_zona')
          ->orderBy('v.nombre')->orderByDesc(DB::raw('COUNT(*)'))
          ->selectRaw('v.id_vendedor, v.nombre as vendedor, z.id_zona, z.nombre_zona as zona, COUNT(*) as ventas')
          ->get();

        // 1B) top zona por vendedor
        $agg = DB::table('sales as s')
          ->join('vendors as v','v.id_vendedor','=','s.id_vendedor')
          ->join('zones   as z','z.id_zona','=','s.id_zona')
          ->where('s.estado','<>','ANULADA')
          ->groupBy('v.id_vendedor','v.nombre','z.id_zona','z.nombre_zona')
          ->selectRaw('v.id_vendedor, v.nombre as vendedor, z.id_zona, z.nombre_zona as zona, COUNT(*) as ventas');

        $topZonaPorVendedor = DB::query()
          ->fromSub($agg, 'a')
          ->selectRaw('a.*, ROW_NUMBER() OVER (PARTITION BY a.id_vendedor ORDER BY a.ventas DESC) as rn')
          ->get()
          ->where('rn', 1)
          ->values();

        // 2) zonas sin ventas (si hay fechas)
        $zonasSinVentas = collect();
        if ($desde && $hasta) {
          $zonasSinVentas = DB::table('zones as z')
            ->leftJoin('sales as s', function($j) use ($desde,$hasta) {
              $j->on('s.id_zona','=','z.id_zona')
                ->where('s.estado','<>','ANULADA')
                ->whereBetween('s.fecha', [$desde, $hasta]);
            })
            ->whereNull('s.id_venta')
            ->orderBy('z.nombre_zona')
            ->select('z.id_zona','z.nombre_zona')
            ->get();
        }

        // 3) vendedores sin ventas (si hay fechas)
        $vendedoresSinVentas = collect();
        if ($desde && $hasta) {
          $vendedoresSinVentas = DB::table('vendors as v')
            ->leftJoin('sales as s', function($j) use ($desde,$hasta) {
              $j->on('s.id_vendedor','=','v.id_vendedor')
                ->where('s.estado','<>','ANULADA')
                ->whereBetween('s.fecha', [$desde, $hasta]);
            })
            ->whereNull('s.id_venta')
            ->orderBy('v.nombre')
            ->select('v.id_vendedor','v.nombre')
            ->get();
        }

        // 4) pivot por cliente (2020–2023) + zona principal
        $zcount = DB::table('sales as s')
          ->join('zones as z','z.id_zona','=','s.id_zona')
          ->where('s.estado','<>','ANULADA')
          ->groupBy('s.id_cliente','s.id_zona','z.nombre_zona')
          ->selectRaw('s.id_cliente, s.id_zona, z.nombre_zona, COUNT(*) as ventas_zona');

        $zranked = DB::query()
          ->fromSub($zcount, 'zc')
          ->selectRaw('zc.*, ROW_NUMBER() OVER (PARTITION BY zc.id_cliente ORDER BY zc.ventas_zona DESC) as rn');

        $pivot = DB::table('clients as c')
          ->leftJoin('sales as s', function($j){
            $j->on('s.id_cliente','=','c.id_cliente')
              ->where('s.estado','<>','ANULADA');
          })
          ->groupBy('c.id_cliente','c.nombre')
          ->selectRaw("
            c.id_cliente, c.nombre,
            SUM(CASE WHEN YEAR(s.fecha)=2020 THEN s.monto_total ELSE 0 END) AS ventas_2020,
            SUM(CASE WHEN YEAR(s.fecha)=2021 THEN s.monto_total ELSE 0 END) AS ventas_2021,
            SUM(CASE WHEN YEAR(s.fecha)=2022 THEN s.monto_total ELSE 0 END) AS ventas_2022,
            SUM(CASE WHEN YEAR(s.fecha)=2023 THEN s.monto_total ELSE 0 END) AS ventas_2023
          ");

        $clientSalesPivot = DB::query()
          ->fromSub($pivot, 'p')
          ->leftJoinSub($zranked, 'zr', function($j){
            $j->on('zr.id_cliente','=','p.id_cliente')->where('zr.rn','=',1);
          })
          ->orderBy('p.nombre')
          ->selectRaw('p.*, zr.nombre_zona as zona')
          ->get();

        return Inertia::render('Dashboard/Index', [
          'filters' => ['desde' => $desde, 'hasta' => $hasta],
          'zonasPorVendedor'    => $zonasPorVendedor,
          'topZonaPorVendedor'  => $topZonaPorVendedor,
          'zonasSinVentas'      => $zonasSinVentas,
          'vendedoresSinVentas' => $vendedoresSinVentas,
          'clientSalesPivot'    => $clientSalesPivot,
        ]);
    }
}
