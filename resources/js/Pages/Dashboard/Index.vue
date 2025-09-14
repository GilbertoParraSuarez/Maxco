<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import {
  ChartBarIcon,
  MapPinIcon,
  UserGroupIcon,
  CurrencyDollarIcon,
  CalendarIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  filters: Object,
  zonasPorVendedor: Array,
  topZonaPorVendedor: Array,
  zonasSinVentas: Array,
  vendedoresSinVentas: Array,
  clientSalesPivot: Array,
})

// Estado reactivo
const filters = ref({
  desde: props.filters?.desde || '',
  hasta: props.filters?.hasta || ''
})

const isLoading = ref(false)

// Computadas
const hasDateFilters = computed(() => filters.value.desde && filters.value.hasta)

const stats = computed(() => ({
  totalVendedores: props.topZonaPorVendedor?.length || 0,
  totalZonas: new Set(props.zonasPorVendedor?.map(z => z.id_zona)).size || 0,
  totalClientes: props.clientSalesPivot?.length || 0,
  zonasSinActividad: props.zonasSinVentas?.length || 0
}))

const totalVentasPorAnio = computed(() => {
  if (!props.clientSalesPivot) return {}
  return props.clientSalesPivot.reduce((acc, cliente) => {
    acc['2020'] = (acc['2020'] || 0) + parseFloat(cliente.ventas_2020 || 0)
    acc['2021'] = (acc['2021'] || 0) + parseFloat(cliente.ventas_2021 || 0)
    acc['2022'] = (acc['2022'] || 0) + parseFloat(cliente.ventas_2022 || 0)
    acc['2023'] = (acc['2023'] || 0) + parseFloat(cliente.ventas_2023 || 0)
    return acc
  }, {})
})

// Métodos
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-EC', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2
  }).format(Number(amount || 0))
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('es-EC')
}

const applyFilters = async () => {
  if (!hasDateFilters.value) {
    alert('Por favor selecciona ambas fechas para aplicar los filtros.')
    return
  }

  if (new Date(filters.value.desde) > new Date(filters.value.hasta)) {
    alert('La fecha "Desde" no puede ser mayor que la fecha "Hasta".')
    return
  }

  isLoading.value = true

  try {
    await router.get(
      route('dashboard'),
      {
        desde: filters.value.desde,
        hasta: filters.value.hasta
      },
      {
        preserveScroll: true,
        replace: true,
        onFinish: () => {
          isLoading.value = false
        }
      }
    )
  } catch (error) {
    isLoading.value = false
    console.error('Error al aplicar filtros:', error)
  }
}

const clearFilters = () => {
  filters.value.desde = ''
  filters.value.hasta = ''
  router.get(route('dashboard'), {}, { preserveScroll: true, replace: true })
}

// Helpers de CSV
const csvEscape = (val) => {
  if (val === null || val === undefined) return ''
  const s = String(val).replace(/"/g, '""')
  // Si hay coma, salto de línea o comillas, envolvemos en comillas
  return /[",\n]/.test(s) ? `"${s}"` : s
}
const pick = (obj, path) => {
  // admite keys simples tipo "ventas_2020"
  return obj?.[path] ?? ''
}

const makeFilename = (base) => {
  const d = new Date()
  const y = d.getFullYear(), m = String(d.getMonth() + 1).padStart(2, '0'), day = String(d.getDate()).padStart(2, '0')
  const rango = (filters.value.desde && filters.value.hasta)
    ? `_${filters.value.desde}_a_${filters.value.hasta}`
    : ''
  return `${base}${rango}_${y}${m}${day}.csv`
}

const buildCSV = (rows, columns) => {
  const header = columns.map(c => csvEscape(c.label)).join(',')
  const body = (rows || []).map(r =>
    columns.map(c => csvEscape(c.format ? c.format(pick(r, c.key), r) : pick(r, c.key))).join(',')
  ).join('\n')
  // BOM para Excel
  return '\uFEFF' + header + '\n' + body
}

const downloadCSV = (csvString, filename) => {
  const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

/** API pública: exportación genérica */
const exportToCSV = (rows, filenameBase, columns) => {
  if (!rows?.length) {
    alert('No hay datos para exportar.')
    return
  }
  const csv = buildCSV(rows, columns)
  downloadCSV(csv, makeFilename(filenameBase))
}

/** Columnas por sección */
const colsZonasPorVendedor = [
  { key: 'vendedor', label: 'Vendedor' },
  { key: 'zona', label: 'Zona' },
  {
    key: 'ventas', label: 'Ventas',
    format: (v) => Number(v ?? 0).toFixed(2)
  }
]

const colsTopZonaPorVendedor = [
  { key: 'vendedor', label: 'Vendedor' },
  { key: 'zona', label: 'Zona Principal' },
  {
    key: 'ventas', label: 'N° Ventas',
    format: (v) => Number(v ?? 0)
  }
]

const colsZonasSinVentas = [
  { key: 'nombre_zona', label: 'Zona' },
  {
    key: 'estado', label: 'Estado',
    format: () => 'Sin actividad'
  }
]

const colsVendedoresSinVentas = [
  { key: 'nombre', label: 'Vendedor' },
  {
    key: 'estado', label: 'Estado',
    format: () => 'Sin ventas'
  }
]

const colsClientSalesPivot = [
  { key: 'id_cliente', label: 'ID' },
  { key: 'nombre', label: 'Cliente' },
  { key: 'zona', label: 'Zona Principal' },
  { key: 'ventas_2020', label: '2020', format: (v) => Number(v ?? 0).toFixed(2) },
  { key: 'ventas_2021', label: '2021', format: (v) => Number(v ?? 0).toFixed(2) },
  { key: 'ventas_2022', label: '2022', format: (v) => Number(v ?? 0).toFixed(2) },
  { key: 'ventas_2023', label: '2023', format: (v) => Number(v ?? 0).toFixed(2) },
]

// Resumen anual (totales por año)
const colsResumenAnual = [
  { key: 'year', label: 'Año' },
  { key: 'total', label: 'Total', format: (v) => Number(v ?? 0).toFixed(2) }
]
const exportResumenAnual = () => {
  const rows = Object.entries(totalVentasPorAnio.value || {}).map(([year, total]) => ({ year, total }))
  exportToCSV(rows, 'resumen_ventas_anual_2020_2023', colsResumenAnual)
}

/** Wrappers por sección */
const exportZonasPorVendedor = () =>
  exportToCSV(props.zonasPorVendedor, 'zonas_por_vendedor', colsZonasPorVendedor)

const exportTopZonaPorVendedor = () =>
  exportToCSV(props.topZonaPorVendedor, 'top_zona_por_vendedor', colsTopZonaPorVendedor)

const exportZonasSinVentas = () =>
  exportToCSV(props.zonasSinVentas, 'zonas_sin_ventas', colsZonasSinVentas)

const exportVendedoresSinVentas = () =>
  exportToCSV(props.vendedoresSinVentas, 'vendedores_sin_ventas', colsVendedoresSinVentas)

const exportClientesPivot = () =>
  exportToCSV(props.clientSalesPivot, 'ventas_clientes_2020_2023', colsClientSalesPivot)

// Watch para validación en tiempo real
watch(() => filters.value.desde, (newVal) => {
  if (newVal && filters.value.hasta && new Date(newVal) > new Date(filters.value.hasta)) {
    filters.value.hasta = ''
  }
})
</script>

<template>
  <Head title="Dashboard de Ventas" />

  <AuthenticatedLayout>
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard de Ventas</h1>
        <p class="mt-2 text-gray-600">Análisis completo de ventas por zonas y vendedores</p>
      </div>

      <!-- Stats Cards -->
      <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Vendedores Activos -->
        <div class="overflow-hidden rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 shadow-lg ring-1 ring-black/5">
          <div class="p-6">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <UserGroupIcon class="h-8 w-8 text-white/90" />
              </div>
              <div class="min-w-0 flex-1">
                <dl>
                  <dt class="truncate text-sm font-medium text-white/80">Vendedores Activos</dt>
                  <dd class="mt-1 text-2xl font-semibold text-white/80">{{ stats.totalVendedores }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Zonas Activas -->
        <div class="overflow-hidden rounded-xl bg-gradient-to-r from-green-500 to-green-600 shadow-lg ring-1 ring-black/5">
          <div class="p-6">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <MapPinIcon class="h-8 w-8 text-white/90" />
              </div>
              <div class="min-w-0 flex-1">
                <dl>
                  <dt class="truncate text-sm font-medium text-white/80">Zonas Activas</dt>
                  <dd class="mt-1 text-2xl font-semibold text-white/80">{{ stats.totalZonas }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Clientes -->
        <div class="overflow-hidden rounded-xl bg-gradient-to-r from-purple-500 to-purple-600 shadow-lg ring-1 ring-black/5">
          <div class="p-6">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <CurrencyDollarIcon class="h-8 w-8 text-white/90" />
              </div>
              <div class="min-w-0 flex-1">
                <dl>
                  <dt class="truncate text-sm font-medium text-white/80">Total Clientes</dt>
                  <dd class="mt-1 text-2xl font-semibold text-white/80">{{ stats.totalClientes }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Zonas Sin Actividad -->
        <div class="overflow-hidden rounded-xl bg-gradient-to-r from-red-500 to-red-600 shadow-lg ring-1 ring-black/5">
          <div class="p-6">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <ExclamationTriangleIcon class="h-8 w-8 text-white/90" />
              </div>
              <div class="min-w-0 flex-1">
                <dl>
                  <dt class="truncate text-sm font-medium text-white/80">
                    Zonas Sin Actividad
                  </dt>
                  <dd class="mt-1 text-2xl font-semibold text-white/80">
                    {{ stats.zonasSinActividad }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Filtros -->
      <div class="mb-8 overflow-hidden rounded-xl bg-white shadow-lg border border-gray-100">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
          <div class="flex items-center">
            <CalendarIcon class="h-5 w-5 text-gray-500 mr-2" />
            <h3 class="text-lg font-semibold text-gray-900">Filtros de Fecha</h3>
          </div>
          <p class="mt-1 text-sm text-gray-600">Aplica filtros para ver zonas y vendedores sin ventas en un
            período específico</p>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 lg:grid-cols-5">
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">Fecha Desde</label>
              <input v-model="filters.desde" type="date"
                     class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                     :max="filters.hasta" />
            </div>

            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">Fecha Hasta</label>
              <input v-model="filters.hasta" type="date"
                     class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                     :min="filters.desde" />
            </div>

            <div class="flex items-end space-x-3">
              <button @click="applyFilters" :disabled="isLoading || !hasDateFilters"
                      class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="isLoading" class="mr-2">
                  <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                </span>
                {{ isLoading ? 'Aplicando...' : 'Aplicar Filtros' }}
              </button>

              <button @click="clearFilters"
                      class="inline-flex items-center justify-center rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                Limpiar
              </button>
            </div>

            <div v-if="hasDateFilters" class="col-span-2 flex items-center text-sm text-gray-600">
              <CheckCircleIcon class="h-4 w-4 text-green-500 mr-2" />
              Período: {{ formatDate(filters.desde) }} - {{ formatDate(filters.hasta) }}
            </div>
          </div>
        </div>
      </div>

      <!-- 1) Análisis de Zonas por Vendedor -->
      <div class="mb-8 grid gap-8 lg:grid-cols-2">
        <!-- Top Zona por Vendedor -->
        <div class="overflow-hidden rounded-xl bg-white shadow-lg border border-gray-100">
          <div class="bg-gradient-to-r from-indigo-50 to-blue-50 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Zona TOP por Vendedor</h3>
                <p class="text-sm text-gray-600">La zona con mayor volumen de ventas para cada vendedor</p>
              </div>
              <div class="flex items-center gap-3">
                <button @click="exportTopZonaPorVendedor"
                        class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                  Exportar
                </button>
                <ChartBarIcon class="h-8 w-8 text-indigo-500" />
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Vendedor</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Zona Principal</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  N° Ventas</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="(record, index) in topZonaPorVendedor"
                  :key="`${record.id_vendedor}-${record.id_zona}`"
                  :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="font-medium text-gray-900">{{ record.vendedor }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ record.zona }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <span class="text-lg font-bold text-indigo-600">{{ record.ventas }}</span>
                </td>
              </tr>
              <tr v-if="!topZonaPorVendedor?.length">
                <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                  <div class="flex flex-col items-center">
                    <ExclamationTriangleIcon class="h-12 w-12 text-gray-300 mb-2" />
                    <span>No hay datos disponibles</span>
                  </div>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Todas las Zonas por Vendedor -->
        <div class="overflow-hidden rounded-xl bg-white shadow-lg border border-gray-100">
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Detalle Completo</h3>
                <p class="text-sm text-gray-600">Todas las zonas y sus ventas por vendedor</p>
              </div>
              <button @click="exportZonasPorVendedor"
                      class="text-sm text-green-600 hover:text-green-700 font-medium">
                Exportar
              </button>
            </div>
          </div>

          <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50 sticky top-0">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Vendedor</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Zona</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Ventas</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="record in zonasPorVendedor" :key="`${record.id_vendedor}-${record.id_zona}`"
                  class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900">{{ record.vendedor }}</td>
                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-600">{{ record.zona }}</td>
                <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                  {{ record.ventas }}</td>
              </tr>
              <tr v-if="!zonasPorVendedor?.length">
                <td colspan="3" class="px-6 py-8 text-center text-gray-500">Sin datos disponibles</td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 2) Zonas sin Ventas -->
      <div class="mb-8 overflow-hidden rounded-xl bg-white shadow-lg border border-gray-100">
        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Zonas Sin Actividad</h3>
              <p class="text-sm text-gray-600">
                {{ hasDateFilters ? `Zonas sin ventas entre ${formatDate(filters.desde)} y ${formatDate(filters.hasta)}` : 'Selecciona un período para ver zonas inactivas' }}
              </p>
            </div>
            <div class="flex items-center gap-3">
              <button
                @click="exportZonasSinVentas"
                class="text-sm text-yellow-700 hover:text-yellow-800 font-medium"
              >
                Exportar
              </button>
              <ExclamationTriangleIcon class="h-8 w-8 text-yellow-500" />
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Zona</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Estado</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="zona in zonasSinVentas" :key="zona.id_zona" class="hover:bg-yellow-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="font-medium text-gray-900">{{ zona.nombre_zona }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  Sin actividad
                </span>
              </td>
            </tr>
            <tr v-if="!zonasSinVentas?.length">
              <td colspan="2" class="px-6 py-8 text-center text-gray-500">
                {{ hasDateFilters ? 'Todas las zonas tienen actividad en el período seleccionado' :
                'Selecciona un período de fechas para ver zonas sin actividad' }}
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 3) Vendedores sin Ventas -->
      <div class="mb-8 overflow-hidden rounded-xl bg-white shadow-lg border border-gray-100">
        <div class="bg-gradient-to-r from-red-50 to-pink-50 px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Vendedores Sin Actividad</h3>
              <p class="text-sm text-gray-600">
                {{ hasDateFilters ? `Vendedores sin ventas entre ${formatDate(filters.desde)} y ${formatDate(filters.hasta)}` : 'Selecciona un período para ver vendedores inactivos' }}
              </p>
            </div>
            <div class="flex items-center gap-3">
              <button
                @click="exportVendedoresSinVentas"
                class="text-sm text-red-600 hover:text-red-700 font-medium"
              >
                Exportar
              </button>
              <UserGroupIcon class="h-8 w-8 text-red-500" />
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Vendedor</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Estado</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="vendedor in vendedoresSinVentas" :key="vendedor.id_vendedor" class="hover:bg-red-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="font-medium text-gray-900">{{ vendedor.nombre }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  Sin ventas
                </span>
              </td>
            </tr>
            <tr v-if="!vendedoresSinVentas?.length">
              <td colspan="2" class="px-6 py-8 text-center text-gray-500">
                {{ hasDateFilters ? 'Todos los vendedores tienen actividad en el período seleccionado' : 'Selecciona un período de fechas para ver vendedores sin actividad'
                }}
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 4) Resumen de Ventas por Años -->
      <div v-if="Object.keys(totalVentasPorAnio).length"
           class="mb-8 overflow-hidden rounded-xl bg-white shadow-lg border border-gray-100">
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Resumen de Ventas por Año</h3>
              <p class="text-sm text-gray-600">Total de ventas consolidado (2020-2023)</p>
            </div>
            <button @click="exportResumenAnual"
                    class="text-sm text-purple-700 hover:text-purple-800 font-medium">
              Exportar
            </button>
          </div>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-2 gap-6 sm:grid-cols-4">
            <div v-for="(total, year) in totalVentasPorAnio" :key="year" class="text-center">
              <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(total) }}</div>
              <div class="text-sm font-medium text-gray-500">{{ year }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- 5) Ventas por Cliente (Pivot 2020-2023) -->
      <div class="mb-8 overflow-hidden rounded-xl bg-white shadow-lg border border-gray-100">
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Histórico de Ventas por Cliente</h3>
              <p class="text-sm text-gray-600">Análisis comparativo de ventas 2020-2023 • Zona = zona con
                mayor actividad del cliente</p>
            </div>
            <button @click="exportClientesPivot"
                    class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
              Exportar
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                ID</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Cliente</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Zona Principal</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                2020</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                2021</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                2022</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                2023</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="(cliente, index) in clientSalesPivot"
                :key="cliente.id_cliente"
                :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                class="hover:bg-emerald-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm font-mono text-gray-500">#{{ cliente.id_cliente }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ cliente.nombre }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="cliente.zona"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                  {{ cliente.zona }}
                </span>
                <span v-else class="text-sm text-gray-400">—</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm"
                  :class="parseFloat(cliente.ventas_2020) > 0 ? 'font-semibold text-gray-900' : 'text-gray-400'">
                {{ formatCurrency(cliente.ventas_2020) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm"
                  :class="parseFloat(cliente.ventas_2021) > 0 ? 'font-semibold text-gray-900' : 'text-gray-400'">
                {{ formatCurrency(cliente.ventas_2021) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm"
                  :class="parseFloat(cliente.ventas_2022) > 0 ? 'font-semibold text-gray-900' : 'text-gray-400'">
                {{ formatCurrency(cliente.ventas_2022) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm"
                  :class="parseFloat(cliente.ventas_2023) > 0 ? 'font-semibold text-gray-900' : 'text-gray-400'">
                {{ formatCurrency(cliente.ventas_2023) }}
              </td>
            </tr>
            <tr v-if="!clientSalesPivot?.length">
              <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                <div class="flex flex-col items-center">
                  <CurrencyDollarIcon class="h-12 w-12 text-gray-300 mb-2" />
                  <span>No hay datos de ventas disponibles</span>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Animaciones personalizadas */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-in {
  animation: fadeIn 0.3s ease-out;
}

/* Hover effects para las tablas */
.table-row-hover:hover {
  background-color: rgba(59, 130, 246, 0.05);
  transform: translateX(2px);
  transition: all 0.2s ease-in-out;
}

/* Efectos de gradiente para los headers */
.gradient-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Responsive improvements */
@media (max-width: 640px) {
  .stats-card {
    padding: 1rem;
  }

  .stats-card h3 {
    font-size: 0.875rem;
  }

  .stats-card .stat-number {
    font-size: 1.5rem;
  }
}

/* Scrollbar personalizada */
.overflow-y-auto::-webkit-scrollbar {
  width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
