<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import {
  PlusIcon,
  PencilSquareIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  EyeIcon,
  EyeSlashIcon,
  CurrencyDollarIcon,
  ArchiveBoxIcon,
  TagIcon
} from '@heroicons/vue/24/outline'
import { computed, onMounted, watch } from 'vue'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

/** Props: acepta paginador plano o con meta */
const props = defineProps({
  products: {
    type: Object,
    default: () => ({
      data: [],
      links: [],
      total: 0, from: 0, to: 0,
      meta: { total: 0, from: 0, to: 0 }
    })
  }
})

/** Normalización */
const collection = computed(() => {
  const data  = Array.isArray(props.products?.data) ? props.products.data : []
  const links = Array.isArray(props.products?.links) ? props.products.links : []
  const from  = props.products?.meta?.from  ?? props.products?.from  ?? 0
  const to    = props.products?.meta?.to    ?? props.products?.to    ?? 0
  const total = props.products?.meta?.total ?? props.products?.total ?? 0
  return { data, links, meta: { from, to, total } }
})

const paginationInfo = computed(() => collection.value.meta)
const totalItems = computed(() => paginationInfo.value.total || 0)

/** Prev/Next + páginas */
const isPrev = (l) => typeof l.label === 'string' && (/\bprev/i.test(l.label) || /anterior/i.test(l.label) || /«/u.test(l.label))
const isNext = (l) => typeof l.label === 'string' && (/\bnext/i.test(l.label) || /siguiente/i.test(l.label) || /»/u.test(l.label))
const prevLink = computed(() => collection.value.links.find(isPrev))
const nextLink = computed(() => collection.value.links.find(isNext))
const pageLinks = computed(() => collection.value.links.filter(l => !isPrev(l) && !isNext(l)))

/** Helpers de UI */
const toast = (title = 'Hecho', icon = 'success') =>
  Swal.fire({ title, icon, toast: true, position: 'top-end', timer: 2200, showConfirmButton: false })

const formatMoney = (val) => {
  const n = Number(val ?? 0)
  try {
    return new Intl.NumberFormat('es-EC', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 }).format(n)
  } catch {
    return `$${n.toFixed(2)}`
  }
}

/** Toggle de estado con confirmación */
const toggleProductStatus = async (p) => {
  const activando = !p.activo
  const { isConfirmed } = await Swal.fire({
    title: activando ? '¿Activar producto?' : '¿Desactivar producto?',
    text: activando ? 'El producto se mostrará como disponible.' : 'El producto quedará inactivo.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: activando ? 'Sí, activar' : 'Sí, desactivar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true,
    confirmButtonColor: activando ? '#16a34a' : '#dc2626',
  })
  if (!isConfirmed) return

  router.post(
    route('products.toggle', { product: p.id_producto }),
    {},
    {
      preserveScroll: true,
      onStart: () => Swal.fire({ title: activando ? 'Activando...' : 'Desactivando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() }),
      onFinish: () => Swal.close(),
      onSuccess: () => toast(activando ? 'Producto activado' : 'Producto desactivado', 'success'),
      onError: () => Swal.fire('Error', 'No se pudo actualizar el estado.', 'error')
    }
  )
}

/** Toast por flashes backend */
const page = usePage()
const successFlash = computed(() => page?.props?.flash?.success ?? null)
onMounted(() => { if (successFlash.value) toast(successFlash.value, 'success') })
watch(successFlash, (val) => { if (val) toast(val, 'success') })
</script>

<template>
  <Head title="Productos" />
  <AuthenticatedLayout>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <!-- Stats -->
      <div class="mb-6">
        <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="h-2 w-2 rounded-full bg-green-500"></div>
              <span class="text-sm font-medium text-gray-900">Total de Productos</span>
            </div>
            <span class="text-2xl font-bold text-indigo-600">{{ totalItems }}</span>
          </div>
        </div>
      </div>

      <!-- Table card -->
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Listado de Productos</h3>
            <Link
              :href="route('products.create')"
              class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
            >
              <PlusIcon class="h-4 w-4" />
              Nuevo Producto
            </Link>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Precio</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="p in collection.data" :key="p.id_producto" class="hover:bg-gray-50" :class="{ 'opacity-60': !p.activo }">
                <td class="px-6 py-4">#{{ p.id_producto }}</td>
                <td class="px-6 py-4">
                  <div class="font-semibold text-gray-900 flex items-center gap-2">
                    <TagIcon class="h-4 w-4 text-gray-400" />
                    {{ p.nombre || 'Sin nombre' }}
                  </div>
                  <div class="mt-0.5 text-xs text-gray-500">
                    <span v-if="p.categoria">Categoría: {{ p.categoria }}</span>
                    <span v-if="p.sku" class="ml-2">• SKU: {{ p.sku }}</span>
                  </div>
                  <div v-if="p.descripcion" class="mt-1 text-xs text-gray-500 line-clamp-1">{{ p.descripcion }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2 text-sm text-gray-900">
                    <CurrencyDollarIcon class="h-4 w-4 text-gray-400" />
                    {{ formatMoney(p.precio) }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                       :class="p.stock > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
                    <ArchiveBoxIcon class="h-4 w-4 mr-1" />
                    {{ p.stock }} unid.
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="p.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                    <span :class="p.activo ? 'bg-green-400' : 'bg-red-400'" class="mr-1.5 h-2 w-2 rounded-full"></span>
                    {{ p.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="route('products.edit', { product: p.id_producto })"
                      class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100"
                    >
                      <PencilSquareIcon class="h-4 w-4" /> Editar
                    </Link>
                    <button
                      @click="toggleProductStatus(p)"
                      class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                      :class="p.activo
                        ? 'bg-red-50 text-red-700 hover:bg-red-100 focus:ring-red-500'
                        : 'bg-green-50 text-green-700 hover:bg-green-100 focus:ring-green-500'">
                      <EyeSlashIcon v-if="p.activo" class="h-4 w-4" />
                      <EyeIcon v-else class="h-4 w-4" />
                      {{ p.activo ? 'Desactivar' : 'Activar' }}
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="collection.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No hay productos registrados.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="collection.data.length>0" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
          <div class="flex items-center justify-between">
            <p class="hidden sm:block text-sm text-gray-700">
              Mostrando <span class="font-medium">{{ paginationInfo.from || 0 }}</span> a
              <span class="font-medium">{{ paginationInfo.to || 0 }}</span> de
              <span class="font-medium">{{ totalItems }}</span> resultados
            </p>

            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
              <Link v-if="prevLink?.url" :href="prevLink.url" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-600 ring-1 ring-gray-300 hover:bg-indigo-50 hover:text-indigo-700">‹</Link>
              <span v-else class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-300 ring-1 ring-gray-300">‹</span>

              <Link v-for="link in pageLinks" :key="link.url ?? link.label" :href="link.url || '#'" v-html="link.label"
                    :aria-current="link.active ? 'page' : null"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-gray-300 hover:bg-indigo-50 hover:text-indigo-700 transition-colors"
                    :class="{
                      'z-10 bg-indigo-600 text-white': link.active,
                      'text-gray-900': !link.active && link.url,
                      'text-gray-300 cursor-not-allowed': !link.url
                    }" />
              <Link v-if="nextLink?.url" :href="nextLink.url" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-600 ring-1 ring-gray-300 hover:bg-indigo-50 hover:text-indigo-700">›</Link>
              <span v-else class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-300 ring-1 ring-gray-300">›</span>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
