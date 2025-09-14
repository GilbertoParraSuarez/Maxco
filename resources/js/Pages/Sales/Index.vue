<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { PlusIcon, ChevronLeftIcon, ChevronRightIcon, EyeIcon, XCircleIcon } from '@heroicons/vue/24/outline'
import { computed } from 'vue'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

const props = defineProps({
  sales: {
    type: Object,
    default: () => ({
      data: [],
      links: [],
      total: 0, from: 0, to: 0,
      meta: { total: 0, from: 0, to: 0 }
    })
  }
})

const collection = computed(() => {
  const data  = Array.isArray(props.sales?.data) ? props.sales.data : []
  const links = Array.isArray(props.sales?.links) ? props.sales.links : []
  const from  = props.sales?.meta?.from  ?? props.sales?.from  ?? 0
  const to    = props.sales?.meta?.to    ?? props.sales?.to    ?? 0
  const total = props.sales?.meta?.total ?? props.sales?.total ?? 0
  return { data, links, meta: { from, to, total } }
})
const paginationInfo = computed(() => collection.value.meta)
const totalItems = computed(() => paginationInfo.value.total || 0)

const isPrev = (l) => typeof l.label === 'string' && (/\bprev/i.test(l.label) || /anterior/i.test(l.label) || /«/u.test(l.label))
const isNext = (l) => typeof l.label === 'string' && (/\bnext/i.test(l.label) || /siguiente/i.test(l.label) || /»/u.test(l.label))
const prevLink = computed(() => collection.value.links.find(isPrev))
const nextLink = computed(() => collection.value.links.find(isNext))
const pageLinks = computed(() => collection.value.links.filter(l => !isPrev(l) && !isNext(l)))

const money = (n, cur='USD') => new Intl.NumberFormat('es-EC',{ style:'currency', currency: cur }).format(Number(n||0))

const cancelSale = (sale) => {
  Swal.fire({
    title: 'Anular venta',
    text: `¿Seguro que deseas anular la venta #${sale.id_venta}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, anular',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#ef4444'
  }).then((res) => {
    if(!res.isConfirmed) return
    router.post(route('sales.cancel', { sale: sale.id_venta }), {}, {
      preserveScroll: true,
      onSuccess: () => Swal.fire({ icon:'success', title:'Venta anulada', timer:1500, showConfirmButton:false }),
      onError: () => Swal.fire('Error', 'No se pudo anular la venta.', 'error')
    })
  })
}
</script>

<template>
  <Head title="Ventas" />
  <AuthenticatedLayout>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <!-- Estadística -->
      <div class="mb-6">
        <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <div class="h-2 w-2 rounded-full bg-green-500"></div>
              <span class="text-sm font-medium text-gray-900">Total de Ventas</span>
            </div>
            <span class="text-2xl font-bold text-indigo-600">{{ totalItems }}</span>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="overflow-hidden rounded-xl bg-white shadow-lg border border-gray-200">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Listado de Ventas</h3>
            <div>
              <Link
                :href="route('sales.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              >
                <PlusIcon class="h-4 w-4" />
                Nueva Venta
              </Link>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vendedor</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Zona</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr
                v-for="s in collection.data"
                :key="s.id_venta"
                class="hover:bg-gray-50 transition-colors"
                :class="{ 'opacity-60': s.estado === 'ANULADA' }"
              >
                <td class="px-6 py-4 whitespace-nowrap">#{{ s.id_venta }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ new Date(s.fecha).toLocaleString('es-EC') }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ s.customer?.nombre || '—' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ s.vendor?.nombre || '—' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ s.zone?.nombre_zona || '—' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right font-semibold">{{ money(s.monto_total, s.moneda) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="{
                      'bg-green-100 text-green-800': s.estado === 'confirmada',
                      'bg-yellow-100 text-yellow-800': s.estado === 'pendiente',
                      'bg-red-100 text-red-800': s.estado === 'anulada'
                    }"
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                  >
                    <span
                      :class="{
                        'bg-green-400': s.estado === 'confirmada',
                        'bg-yellow-400': s.estado === 'pendiente',
                        'bg-red-400': s.estado === 'anulada'
                      }"
                      class="mr-1.5 h-2 w-2 rounded-full"
                    ></span>
                    {{ s.estado }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="inline-flex items-center gap-2">
                    <Link
                      :href="route('sales.show', { sale: s.id_venta })"
                      class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100"
                    >
                      <EyeIcon class="h-4 w-4" /> Ver
                    </Link>

                    <button
                      v-if="s.estado !== 'ANULADA'"
                      @click="cancelSale(s)"
                      class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100"
                    >
                      <XCircleIcon class="h-4 w-4" /> Anular
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="collection.data.length === 0">
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">No hay ventas registradas.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="collection.data.length > 0" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex flex-1 justify-between sm:hidden">
              <Link
                v-if="prevLink?.url"
                :href="prevLink.url"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-700"
              >Anterior</Link>
              <Link
                v-if="nextLink?.url"
                :href="nextLink.url"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-700"
              >Siguiente</Link>
            </div>

            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
              <p class="text-sm text-gray-700">
                Mostrando
                <span class="font-medium">{{ paginationInfo.from || 0 }}</span>
                a
                <span class="font-medium">{{ paginationInfo.to || 0 }}</span>
                de
                <span class="font-medium">{{ totalItems }}</span>
                resultados
              </p>

              <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <Link
                  v-if="prevLink?.url"
                  :href="prevLink.url"
                  class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-600 ring-1 ring-inset ring-gray-300 hover:bg-indigo-50 hover:text-indigo-700 focus:z-20"
                >
                  <span class="sr-only">Anterior</span>
                  <ChevronLeftIcon class="h-5 w-5" />
                </Link>
                <span v-else class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
                  <ChevronLeftIcon class="h-5 w-5" />
                </span>

                <Link
                  v-for="link in pageLinks"
                  :key="link.url ?? link.label"
                  :href="link.url || '#'"
                  v-html="link.label"
                  :aria-current="link.active ? 'page' : null"
                  class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-20
                         hover:bg-indigo-50 hover:text-indigo-700 transition-colors"
                  :class="{
                    'z-10 bg-indigo-600 text-white': link.active,
                    'text-gray-900': !link.active && link.url,
                    'text-gray-300 cursor-not-allowed': !link.url
                  }"
                />

                <Link
                  v-if="nextLink?.url"
                  :href="nextLink.url"
                  class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-600 ring-1 ring-inset ring-gray-300 hover:bg-indigo-50 hover:text-indigo-700 focus:z-20"
                >
                  <span class="sr-only">Siguiente</span>
                  <ChevronRightIcon class="h-5 w-5" />
                </Link>
                <span v-else class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
                  <ChevronRightIcon class="h-5 w-5" />
                </span>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
