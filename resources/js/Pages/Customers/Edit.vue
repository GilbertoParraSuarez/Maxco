<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  customer: {
    type: Object,
    default: () => ({
      id_cliente: null,
      nombre: '',
      email: '',
      telefono: '',
      direccion: ''
    })
  }
})

const customerSafe = computed(() => ({
  id_cliente: props.customer?.id_cliente ?? null,
  nombre: props.customer?.nombre ?? '',
  email: props.customer?.email ?? '',
  telefono: props.customer?.telefono ?? '',
  direccion: props.customer?.direccion ?? ''
}))

const form = useForm({
  nombre: customerSafe.value.nombre,
  email: customerSafe.value.email,
  telefono: customerSafe.value.telefono,
  direccion: customerSafe.value.direccion
})

const submit = () => {
  form.put(route('customers.update', customerSafe.value.id_cliente), {
    onSuccess: () => {
      Swal.fire({
        title: '¡Cambios guardados!',
        text: 'El cliente ha sido actualizado correctamente.',
        icon: 'success',
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#4F46E5'
      }).then(() => {
        // Redirigir al listado tras confirmar
        window.location.href = route('customers.index')
      })
    },
    onError: () => {
      // Muestra el primer error disponible o un mensaje genérico
      const firstError =
        form.errors.nombre ||
        form.errors.email ||
        form.errors.telefono ||
        form.errors.direccion ||
        'No se pudo actualizar el cliente. Verifique los datos e inténtelo nuevamente.'

      Swal.fire({
        title: 'Error al actualizar',
        text: firstError,
        icon: 'error',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#DC2626'
      })
    }
  })
}

/** Monograma para avatar */
const initial = computed(() =>
  (customerSafe.value.nombre || '?').toString().trim().charAt(0).toUpperCase()
)
</script>

<template>
  <Head title="Editar Cliente" />
  <AuthenticatedLayout>
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
      <!-- Card -->
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <!-- Card header -->
        <div class="border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-white px-6 py-5">
          <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-full bg-indigo-600 text-white flex items-center justify-center font-semibold">
              {{ initial }}
            </div>
            <div class="min-w-0 mt-4 mb-4">
              <h1 class="text-lg sm:text-xl font-semibold text-gray-900">
                Editar cliente
                <span class="text-gray-400 font-normal">#{{ customerSafe.id_cliente }}</span>
              </h1>
              <p class="mt-0.5 text-sm text-gray-500">
                Actualiza la información del cliente. Los cambios se guardan al presionar “Actualizar”.
              </p>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" novalidate>
          <div class="px-6 py-6">
            <!-- Sección: Información básica -->
            <div class="mb-6">
              <h2 class="text-sm font-semibold text-gray-900">Información básica</h2>
              <p class="text-sm text-gray-500">Datos principales del registro.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <!-- Nombre -->
              <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                <input
                  id="nombre"
                  v-model="form.nombre"
                  type="text"
                  autocomplete="name"
                  placeholder="Ej. Juan Pérez"
                  :aria-invalid="!!form.errors.nombre"
                  :aria-describedby="form.errors.nombre ? 'error-nombre' : undefined"
                  class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm placeholder:text-gray-400
                         focus:border-indigo-500 focus:ring-indigo-500"
                />
                <p v-if="form.errors.nombre" id="error-nombre" class="mt-1 text-sm text-red-600">
                  {{ form.errors.nombre }}
                </p>
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  autocomplete="email"
                  placeholder="correo@dominio.com"
                  :aria-invalid="!!form.errors.email"
                  :aria-describedby="form.errors.email ? 'error-email' : undefined"
                  class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm placeholder:text-gray-400
                         focus:border-indigo-500 focus:ring-indigo-500"
                />
                <p v-if="form.errors.email" id="error-email" class="mt-1 text-sm text-red-600">
                  {{ form.errors.email }}
                </p>
              </div>

              <!-- Teléfono -->
              <div>
                <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                <input
                  id="telefono"
                  v-model="form.telefono"
                  type="text"
                  inputmode="tel"
                  autocomplete="tel"
                  placeholder="+593 99 999 9999"
                  :aria-invalid="!!form.errors.telefono"
                  :aria-describedby="form.errors.telefono ? 'error-telefono' : undefined"
                  class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm placeholder:text-gray-400
                         focus:border-indigo-500 focus:ring-indigo-500"
                />
                <p v-if="form.errors.telefono" id="error-telefono" class="mt-1 text-sm text-red-600">
                  {{ form.errors.telefono }}
                </p>
              </div>

              <!-- Dirección (full width en sm:col-span-2) -->
              <div class="sm:col-span-2">
                <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
                <input
                  id="direccion"
                  v-model="form.direccion"
                  type="text"
                  autocomplete="street-address"
                  placeholder="Calle Principal 123, Ciudad"
                  :aria-invalid="!!form.errors.direccion"
                  :aria-describedby="form.errors.direccion ? 'error-direccion' : undefined"
                  class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm placeholder:text-gray-400
                         focus:border-indigo-500 focus:ring-indigo-500"
                />
                <p v-if="form.errors.direccion" id="error-direccion" class="mt-1 text-sm text-red-600">
                  {{ form.errors.direccion }}
                </p>
              </div>
            </div>

            <!-- Separador visual -->
            <div class="mt-8 border-t border-dashed border-gray-200"></div>

            <!-- Tips/ayuda -->
            <div class="mt-6 text-xs text-gray-500">
              Revisa que el correo esté correcto. Puedes dejar teléfono o dirección en blanco si no aplica.
            </div>
          </div>

          <!-- Card footer / actions -->
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/70 px-6 py-4 border-t border-gray-200">
            <div class="text-sm text-gray-500">
              Última modificación del cliente
              <span class="font-medium text-gray-700">#{{ customerSafe.id_cliente }}</span>
            </div>

            <div class="flex items-center justify-end gap-3">
              <Link
                :href="route('customers.index')"
                class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
              >
                Cancelar
              </Link>

              <button
                type="submit"
                :disabled="form.processing || !customerSafe.id_cliente"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm
                       hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                       disabled:opacity-60 disabled:cursor-not-allowed"
              >
                <svg
                  v-if="form.processing"
                  class="h-4 w-4 animate-spin"
                  viewBox="0 0 24 24"
                  fill="none"
                  aria-hidden="true"
                >
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                Actualizar
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
