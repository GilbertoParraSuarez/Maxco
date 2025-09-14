<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  filters: Object,
  zonasPorVendedor: Array,
  topZonaPorVendedor: Array,
  zonasSinVentas: Array,
  vendedoresSinVentas: Array,
  clientSalesPivot: Array,
})

const desde = ref(props.filters?.desde || '')
const hasta = ref(props.filters?.hasta || '')

const submitFechas = () => {
  router.get(route('dashboard'), { desde: desde.value, hasta: hasta.value }, { preserveScroll: true, replace: true })
}

const money = (n) => new Intl.NumberFormat('es-EC',{ style:'currency', currency:'USD' }).format(Number(n||0))
</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <!-- Filtros -->
      <div class="mb-6 rounded-xl bg-white border border-gray-200 p-4">
        <h3 class="text-base font-semibold text-gray-900 mb-3">Filtros por fecha (para #2 y #3)</h3>
        <div class="flex flex-wrap items-end gap-3">
          <div>
            <label class="block text-sm text-gray-700">Desde</label>
            <input v-model="desde" type="date" class="mt-1 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div>
            <label class="block text-sm text-gray-700">Hasta</label>
            <input v-model="hasta" type="date" class="mt-1 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <button @click="submitFechas" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            Aplicar
          </button>
        </div>
      </div>

      <!-- 1) Zonas por vendedor (TOP por vendedor) -->
      <div class="mb-8 grid gap-6 md:grid-cols-2">
        <div class="rounded-xl bg-white border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-900">TOP zona por vendedor</h3>
            <p class="text-xs text-gray-500">La zona con más ventas para cada vendedor.</p>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Vendedor</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Zona</th>
                  <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Ventas</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="r in topZonaPorVendedor" :key="`${r.id_vendedor}-${r.id_zona}`">
                  <td class="px-4 py-2">{{ r.vendedor }}</td>
                  <td class="px-4 py-2">{{ r.zona }}</td>
                  <td class="px-4 py-2 text-right font-semibold">{{ r.ventas }}</td>
                </tr>
                <tr v-if="(topZonaPorVendedor?.length || 0) === 0">
                  <td colspan="3" class="px-4 py-6 text-center text-gray-500">Sin datos.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="rounded-xl bg-white border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-900">Zonas por vendedor (todas)</h3>
            <p class="text-xs text-gray-500">Detalle de ventas por zona para cada vendedor.</p>
          </div>
          <div class="overflow-x-auto max-h-[420px]">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Vendedor</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Zona</th>
                  <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Ventas</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="r in zonasPorVendedor" :key="`${r.id_vendedor}-${r.id_zona}`">
                  <td class="px-4 py-2">{{ r.vendedor }}</td>
                  <td class="px-4 py-2">{{ r.zona }}</td>
                  <td class="px-4 py-2 text-right">{{ r.ventas }}</td>
                </tr>
                <tr v-if="(zonasPorVendedor?.length || 0) === 0">
                  <td colspan="3" class="px-4 py-6 text-center text-gray-500">Sin datos.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 2) Zonas sin ventas (fechas) -->
      <div class="mb-8 rounded-xl bg-white border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
          <h3 class="text-sm font-semibold text-gray-900">Zonas sin ventas en el intervalo</h3>
          <p class="text-xs text-gray-500">Requiere seleccionar fechas.</p>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Zona</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="z in zonasSinVentas" :key="z.id_zona">
                <td class="px-4 py-2">{{ z.nombre_zona }}</td>
              </tr>
              <tr v-if="(zonasSinVentas?.length || 0) === 0">
                <td class="px-4 py-6 text-center text-gray-500">Sin datos o sin intervalo.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 3) Vendedores sin ventas (fechas) -->
      <div class="mb-8 rounded-xl bg-white border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
          <h3 class="text-sm font-semibold text-gray-900">Vendedores sin ventas en el intervalo</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Vendedor</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="v in vendedoresSinVentas" :key="v.id_vendedor">
                <td class="px-4 py-2">{{ v.nombre }}</td>
              </tr>
              <tr v-if="(vendedoresSinVentas?.length || 0) === 0">
                <td class="px-4 py-6 text-center text-gray-500">Todos tienen ventas o sin intervalo.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 4) Pivot de ventas por cliente (2020–2023) -->
      <div class="mb-8 rounded-xl bg-white border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
          <h3 class="text-sm font-semibold text-gray-900">Ventas por cliente (2020–2023)</h3>
          <p class="text-xs text-gray-500">Zona = zona con más ventas del cliente.</p>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Cliente</th>
                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Zona</th>
                <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">2020</th>
                <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">2021</th>
                <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">2022</th>
                <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">2023</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="r in clientSalesPivot" :key="r.id_cliente">
                <td class="px-4 py-2">#{{ r.id_cliente }}</td>
                <td class="px-4 py-2">{{ r.nombre }}</td>
                <td class="px-4 py-2">{{ r.zona || '—' }}</td>
                <td class="px-4 py-2 text-right">{{ money(r.ventas_2020) }}</td>
                <td class="px-4 py-2 text-right">{{ money(r.ventas_2021) }}</td>
                <td class="px-4 py-2 text-right">{{ money(r.ventas_2022) }}</td>
                <td class="px-4 py-2 text-right">{{ money(r.ventas_2023) }}</td>
              </tr>
              <tr v-if="(clientSalesPivot?.length || 0) === 0">
                <td colspan="7" class="px-4 py-6 text-center text-gray-500">Sin datos.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>
