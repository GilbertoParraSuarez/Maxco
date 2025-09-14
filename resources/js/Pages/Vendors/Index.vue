<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import {
  UsersIcon,
  PlusIcon,
  PencilSquareIcon,
  EnvelopeIcon,
  PhoneIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  EyeIcon,
  EyeSlashIcon
} from '@heroicons/vue/24/outline'
import { computed, onMounted, watch } from 'vue'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

/**
 * Acepta tanto el paginador plano de Laravel (total, from, to, links)
 * como la forma con meta/links. Se normaliza a un shape único.
 */
const props = defineProps({
  vendors: {
    type: Object,
    default: () => ({
      data: [],
      links: [],
      // cuando viene plano:
      total: 0, from: 0, to: 0,
      // cuando viene con meta:
      meta: { total: 0, from: 0, to: 0 }
    })
  }
})

/** Normalización segura */
const collection = computed(() => {
  const data  = Array.isArray(props.vendors?.data) ? props.vendors.data : []
  const links = Array.isArray(props.vendors?.links) ? props.vendors.links : []
  const from  = props.vendors?.meta?.from  ?? props.vendors?.from  ?? 0
  const to    = props.vendors?.meta?.to    ?? props.vendors?.to    ?? 0
  const total = props.vendors?.meta?.total ?? props.vendors?.total ?? 0
  return { data, links, meta: { from, to, total } }
})

/** Totales / paginación */
const paginationInfo = computed(() => collection.value.meta)
const totalVendors  = computed(() => paginationInfo.value.total || 0)

/** Prev/Next multi-idioma y con símbolos « » */
const isPrev = (l) =>
  typeof l.label === 'string' &&
  (/\bprev/i.test(l.label) || /anterior/i.test(l.label) || /«/u.test(l.label))
const isNext = (l) =>
  typeof l.label === 'string' &&
  (/\bnext/i.test(l.label) || /siguiente/i.test(l.label) || /»/u.test(l.label))

const prevLink = computed(() => collection.value.links.find(isPrev))
const nextLink = computed(() => collection.value.links.find(isNext))
const pageLinks = computed(() => collection.value.links.filter((l) => !isPrev(l) && !isNext(l)))

/** Toast helper */
const toast = (title = 'Hecho', icon = 'success') =>
  Swal.fire({
    title,
    icon,
    toast: true,
    position: 'top-end',
    timer: 2200,
    showConfirmButton: false
  })

/** Confirma y alterna estado (activo/inactivo) con SweetAlert2 */
const toggleVendorStatus = async (vendor) => {
  const activando = !vendor.activo
  const { isConfirmed } = await Swal.fire({
    title: activando ? '¿Activar proveedor?' : '¿Desactivar proveedor?',
    text: activando
      ? 'El proveedor podrá ser usado nuevamente.'
      : 'El proveedor no podrá ser usado hasta reactivarlo.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: activando ? 'Sí, activar' : 'Sí, desactivar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true,
    confirmButtonColor: activando ? '#16a34a' : '#dc2626'
  })

  if (!isConfirmed) return

  router.post(
    route('vendors.toggle', { vendor: vendor.id_vendedor }),
    {},
    {
      preserveScroll: true,
      onStart: () => {
        Swal.fire({
          title: activando ? 'Activando...' : 'Desactivando...',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        })
      },
      onFinish: () => Swal.close(),
      onSuccess: () => toast(activando ? 'Proveedor activado' : 'Proveedor desactivado', 'success'),
      onError: () => Swal.fire('Error', 'No se pudo actualizar el estado.', 'error')
    }
  )
}

/** Muestra toasts por flashes del backend */
const page = usePage()
const successFlash = computed(() => page?.props?.flash?.success ?? null)
onMounted(() => {
  if (successFlash.value) toast(successFlash.value, 'success')
})
watch(successFlash, (val) => {
  if (val) toast(val, 'success')
})
</script>

<template>
  <Head title="Proveedores" />
  <AuthenticatedLayout>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <!-- Estadísticas -->
      <div class="mb-6">
        <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <div class="h-2 w-2 rounded-full bg-green-500"></div>
              <span class="text-sm font-medium text-gray-900">Total de Proveedores</span>
            </div>
            <span class="text-2xl font-bold text-indigo-600">{{ totalVendors }}</span>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="overflow-hidden rounded-xl bg-white shadow-lg border border-gray-200">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Lista de Proveedores</h3>
            <div class="text-sm text-gray-500">
              <Link
                :href="route('vendors.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              >
                <PlusIcon class="h-4 w-4" />
                Nuevo Proveedor
              </Link>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Proveedor</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contacto</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr
                v-for="v in collection.data"
                :key="v.id_vendedor"
                class="hover:bg-gray-50 transition-colors"
                :class="{ 'opacity-60': !v.activo }"
              >
                <!-- ID -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-medium text-indigo-800">
                    #{{ v.id_vendedor }}
                  </div>
                </td>

                <!-- Proveedor -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 flex-shrink-0">
                      <div class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-400 to-pink-400 flex items-center justify-center">
                        <span class="text-sm font-medium text-white">
                          {{ v?.nombre ? v.nombre.charAt(0).toUpperCase() : '?' }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-semibold text-gray-900">{{ v?.nombre || 'Sin nombre' }}</div>
                      <div class="text-sm text-gray-500">Proveedor #{{ v.id_vendedor }}</div>
                    </div>
                  </div>
                </td>

                <!-- Contacto -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="space-y-1">
                    <div class="flex items-center text-sm text-gray-900">
                      <EnvelopeIcon class="h-4 w-4 text-gray-400 mr-2" />
                      {{ v?.email || 'No especificado' }}
                    </div>
                    <div class="flex items-center text-sm text-gray-500" v-if="v?.telefono">
                      <PhoneIcon class="h-4 w-4 text-gray-400 mr-2" />
                      {{ v.telefono }}
                    </div>
                  </div>
                </td>

                <!-- Estado -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="{
                      'bg-green-100 text-green-800': v.activo,
                      'bg-red-100 text-red-800': !v.activo
                    }"
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                  >
                    <span
                      :class="{
                        'bg-green-400': v.activo,
                        'bg-red-400': !v.activo
                      }"
                      class="mr-1.5 h-2 w-2 rounded-full"
                    ></span>
                    {{ v.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>

                <!-- Acciones -->
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <!-- Editar -->
                    <Link
                      :href="route('vendors.edit', { vendor: v.id_vendedor })"
                      class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 transition-colors hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                      <PencilSquareIcon class="h-4 w-4" />
                      Editar
                    </Link>

                    <!-- Toggle Estado (Swal) -->
                    <button
                      @click="toggleVendorStatus(v)"
                      :class="{
                        'bg-red-50 text-red-700 hover:bg-red-100 focus:ring-red-500': v.activo,
                        'bg-green-50 text-green-700 hover:bg-green-100 focus:ring-green-500': !v.activo
                      }"
                      class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                      :title="v.activo ? 'Desactivar proveedor' : 'Activar proveedor'"
                    >
                      <EyeSlashIcon v-if="v.activo" class="h-4 w-4" />
                      <EyeIcon v-else class="h-4 w-4" />
                      {{ v.activo ? 'Desactivar' : 'Activar' }}
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Vacío -->
              <tr v-if="collection.data.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center">
                    <UsersIcon class="h-12 w-12 text-gray-400 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay proveedores registrados</h3>
                    <p class="text-sm text-gray-500 mb-4">Comienza agregando tu primer proveedor al sistema.</p>
                    <Link
                      :href="route('vendors.create')"
                      class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700"
                    >
                      <PlusIcon class="h-4 w-4" />
                      Crear Primer Proveedor
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="collection.data.length > 0" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
          <div class="flex items-center justify-between">
            <!-- Móvil -->
            <div class="flex flex-1 justify-between sm:hidden">
              <Link
                v-if="prevLink?.url"
                :href="prevLink.url"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-700"
              >
                Anterior
              </Link>
              <Link
                v-if="nextLink?.url"
                :href="nextLink.url"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-700"
              >
                Siguiente
              </Link>
            </div>

            <!-- Desktop -->
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
              <p class="text-sm text-gray-700">
                Mostrando
                <span class="font-medium">{{ paginationInfo.from || 0 }}</span>
                a
                <span class="font-medium">{{ paginationInfo.to || 0 }}</span>
                de
                <span class="font-medium">{{ totalVendors }}</span>
                resultados
              </p>

              <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <!-- Prev -->
                <Link
                  v-if="prevLink?.url"
                  :href="prevLink.url"
                  class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-600 ring-1 ring-inset ring-gray-300 hover:bg-indigo-50 hover:text-indigo-700 focus:z-20"
                >
                  <span class="sr-only">Anterior</span>
                  <ChevronLeftIcon class="h-5 w-5" />
                </Link>
                <span
                  v-else
                  class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed"
                >
                  <ChevronLeftIcon class="h-5 w-5" />
                </span>

                <!-- Pages -->
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

                <!-- Next -->
                <Link
                  v-if="nextLink?.url"
                  :href="nextLink.url"
                  class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-600 ring-1 ring-inset ring-gray-300 hover:bg-indigo-50 hover:text-indigo-700 focus:z-20"
                >
                  <span class="sr-only">Siguiente</span>
                  <ChevronRightIcon class="h-5 w-5" />
                </Link>
                <span
                  v-else
                  class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed"
                >
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
