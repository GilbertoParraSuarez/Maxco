<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
  sale: Object
})

const money = (n) => new Intl.NumberFormat('es-EC', { style:'currency', currency: props.sale?.moneda || 'USD' }).format(Number(n||0))
</script>

<template>
  <Head :title="`Venta #${sale?.id_venta}`" />
  <AuthenticatedLayout>
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Venta #{{ sale.id_venta }}</h3>
            <p class="text-sm text-gray-600">Fecha: {{ new Date(sale.fecha).toLocaleString() }} • Estado: <span class="font-medium">{{ sale.estado }}</span></p>
          </div>
          <Link :href="route('sales.index')" class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50">Volver</Link>
        </div>

        <div class="px-6 py-6 space-y-6">
          <!-- Encabezado -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <div class="text-xs text-gray-500">Cliente</div>
              <div class="font-medium">{{ sale.customer?.nombre || '—' }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-500">Vendedor</div>
              <div class="font-medium">{{ sale.vendor?.nombre || '—' }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-500">Zona</div>
              <div class="font-medium">{{ sale.zone?.nombre_zona || '—' }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-500">Método de pago</div>
              <div class="font-medium">{{ sale.metodo_pago }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-500">Moneda / Tasa</div>
              <div class="font-medium">{{ sale.moneda }} / {{ Number(sale.tasa_cambio || 1).toFixed(4) }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-500">Nº Documento</div>
              <div class="font-medium">{{ sale.numero_documento || '—' }}</div>
            </div>
          </div>

          <div v-if="sale.observaciones" class="text-sm text-gray-600 border-t pt-4">
            <span class="font-semibold text-gray-800">Observaciones:</span> {{ sale.observaciones }}
          </div>

          <!-- Detalle -->
          <div class="overflow-hidden rounded-xl border border-gray-200">
            <div class="bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-900">Detalle</div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cant.</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Precio</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Desc.</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Impuesto</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total línea</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="d in sale.details" :key="d.id_detalle_venta">
                    <td class="px-6 py-4">
                      <div class="font-medium text-gray-900">{{ d.product?.nombre || 'Producto' }}</div>
                      <div class="text-xs text-gray-500">SKU: {{ d.product?.sku || '—' }}</div>
                    </td>
                    <td class="px-6 py-4">{{ Number(d.cantidad).toFixed(2) }} {{ d.unidad || 'unid' }}</td>
                    <td class="px-6 py-4">{{ money(d.precio_unitario) }}</td>
                    <td class="px-6 py-4">- {{ money(d.descuento) }}</td>
                    <td class="px-6 py-4">{{ money(d.impuesto) }}</td>
                    <td class="px-6 py-4 text-right font-semibold">{{ money(d.total_linea) }}</td>
                  </tr>

                  <tr v-if="!sale.details || sale.details.length===0">
                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">Sin detalle.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Totales -->
          <div class="grid md:grid-cols-2 gap-6">
            <div></div>
            <div class="rounded-xl border border-gray-200 p-4 bg-gray-50">
              <dl class="space-y-1">
                <div class="flex justify-between">
                  <dt class="text-sm text-gray-600">Descuento</dt>
                  <dd class="text-sm text-gray-900">- {{ money(sale.descuento_total) }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-gray-600">Impuesto</dt>
                  <dd class="text-sm text-gray-900">{{ money(sale.impuesto_total) }}</dd>
                </div>
                <div class="mt-2 border-t pt-2 flex justify-between">
                  <dt class="text-base font-semibold text-gray-900">Total</dt>
                  <dd class="text-base font-semibold text-indigo-700">{{ money(sale.monto_total) }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
