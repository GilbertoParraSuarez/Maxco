<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import {
  UsersIcon,
  PlusIcon,
  PencilSquareIcon,
  EnvelopeIcon,
  PhoneIcon,
  MapPinIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'
import { computed } from 'vue'

/**
 * Acepta tanto el paginador plano de Laravel (total, from, to, links)
 * como la forma con meta/links. Se normaliza a un shape único.
 */
const props = defineProps({
  customers: {
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
const customers = computed(() => {
  const data  = Array.isArray(props.customers?.data) ? props.customers.data : []
  const links = Array.isArray(props.customers?.links) ? props.customers.links : []

  // toma de meta si existe; si no, toma del root (formato plano de Laravel)
  const from  = props.customers?.meta?.from  ?? props.customers?.from  ?? 0
  const to    = props.customers?.meta?.to    ?? props.customers?.to    ?? 0
  const total = props.customers?.meta?.total ?? props.customers?.total ?? 0

  return { data, links, meta: { from, to, total } }
})

/** Totales / info de paginación ya normalizados */
const paginationInfo = computed(() => customers.value.meta)
const totalClientes  = computed(() => paginationInfo.value.total || 0)

/** Detectores de Prev/Next multi-idioma y con símbolos « » */
const isPrev = (l) =>
  typeof l.label === 'string' &&
  (/\bprev/i.test(l.label) || /anterior/i.test(l.label) || /«/u.test(l.label))

const isNext = (l) =>
  typeof l.label === 'string' &&
  (/\bnext/i.test(l.label) || /siguiente/i.test(l.label) || /»/u.test(l.label))

const prevLink = computed(() => customers.value.links.find(isPrev))
const nextLink = computed(() => customers.value.links.find(isNext))

/** Links numéricos (excluye prev/next) */
const pageLinks = computed(() =>
  customers.value.links.filter((l) => !isPrev(l) && !isNext(l))
)
</script>

<template>
  <Head title="Clientes" />
  <AuthenticatedLayout>
    <!-- <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
            <UsersIcon class="h-6 w-6 text-blue-600" />
          </div>
          <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Clientes</h2>
            <p class="text-sm text-gray-500">Gestiona todos los clientes del sistema</p>
          </div>
        </div>
        <Link
          :href="route('customers.create')"
          class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
          <PlusIcon class="h-4 w-4" />
          Nuevo Cliente
        </Link>
      </div>
    </template> -->

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <!-- Estadísticas -->
      <div class="mb-6">
        <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <div class="h-2 w-2 rounded-full bg-green-500"></div>
              <span class="text-sm font-medium text-gray-900">Total de Clientes</span>
            </div>
            <span class="text-2xl font-bold text-indigo-600">{{ totalClientes }}</span>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="overflow-hidden rounded-xl bg-white shadow-lg border border-gray-200">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Lista de Clientes</h3>
            <div class="text-sm text-gray-500">
              
              <Link
          :href="route('customers.create')"
          class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
          <PlusIcon class="h-4 w-4" />
          Nuevo Cliente
        </Link>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contacto</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ubicación</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr
                v-for="customer in customers.data"
                :key="customer.id_cliente"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-medium text-indigo-800">
                    #{{ customer.id_cliente }}
                  </div>
                </td>

                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 flex-shrink-0">
                      <div class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-400 to-pink-400 flex items-center justify-center">
                        <span class="text-sm font-medium text-white">
                          {{ customer?.nombre ? customer.nombre.charAt(0).toUpperCase() : '?' }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-semibold text-gray-900">{{ customer?.nombre || 'Sin nombre' }}</div>
                      <div class="text-sm text-gray-500">Cliente #{{ customer.id_cliente }}</div>
                    </div>
                  </div>
                </td>

                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="space-y-1">
                    <div class="flex items-center text-sm text-gray-900">
                      <EnvelopeIcon class="h-4 w-4 text-gray-400 mr-2" />
                      {{ customer?.email || 'No especificado' }}
                    </div>
                    <div class="flex items-center text-sm text-gray-500" v-if="customer?.telefono">
                      <PhoneIcon class="h-4 w-4 text-gray-400 mr-2" />
                      {{ customer.telefono }}
                    </div>
                  </div>
                </td>

                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center text-sm text-gray-500">
                    <MapPinIcon class="h-4 w-4 text-gray-400 mr-2" />
                    <span class="truncate max-w-xs">{{ customer?.direccion || 'No especificada' }}</span>
                  </div>
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link
                    :href="route('customers.edit', customer.id_cliente)"
                    class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 transition-colors hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                  >
                    <PencilSquareIcon class="h-4 w-4" />
                    Editar
                  </Link>
                </td>
              </tr>

              <tr v-if="customers.data.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center">
                    <UsersIcon class="h-12 w-12 text-gray-400 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay clientes registrados</h3>
                    <p class="text-sm text-gray-500 mb-4">Comienza agregando tu primer cliente al sistema.</p>
                    <Link
                      :href="route('customers.create')"
                      class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700"
                    >
                      <PlusIcon class="h-4 w-4" />
                      Crear Primer Cliente
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="customers.data.length > 0" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
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
                <span class="font-medium">{{ totalClientes }}</span>
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
