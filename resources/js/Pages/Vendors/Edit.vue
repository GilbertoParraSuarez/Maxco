<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  vendor: {
    type: Object,
    default: () => ({
      id_vendedor: null,
      nombre: '',
      email: '',
      telefono: '',
      // identificacion: '',
    })
  }
})

const v = computed(() => ({
  id_vendedor: props.vendor?.id_vendedor ?? null,
  nombre: props.vendor?.nombre ?? '',
  email: props.vendor?.email ?? '',
  telefono: props.vendor?.telefono ?? '',
  // identificacion: props.vendor?.identificacion ?? '',
}))

const form = useForm({
  nombre: v.value.nombre,
  email: v.value.email,
  telefono: v.value.telefono,
  // identificacion: v.value.identificacion,
})

const submit = () => {
  form.put(route('vendors.update', { vendor: v.value.id_vendedor }), {
    onSuccess: () => {
      Swal.fire({
        title: '¡Proveedor actualizado!',
        text: 'La información del proveedor se ha guardado correctamente.',
        icon: 'success',
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#4F46E5'
      }).then(() => {
        window.location.href = route('vendors.index')
      })
    },
    onError: () => {
      const firstError =
        form.errors.nombre ||
        form.errors.email ||
        form.errors.telefono ||
        form.errors.identificacion ||
        'No se pudo actualizar el proveedor. Verifique los datos e inténtelo nuevamente.'

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

/** Monograma para avatar opcional */
const initial = computed(() =>
  (v.value.nombre || '?').toString().trim().charAt(0).toUpperCase()
)
</script>

<template>
  <Head title="Editar Proveedor" />
  <AuthenticatedLayout>
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
      <!-- Card -->
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <!-- Header -->
        <div class="border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-white px-6 py-5">
          <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-full bg-indigo-600 text-white flex items-center justify-center font-semibold">
              {{ initial }}
            </div>
            <div class="min-w-0">
              <h1 class="text-lg sm:text-xl font-semibold text-gray-900">
                Editar proveedor
                <span class="text-gray-400 font-normal">#{{ v.id_vendedor }}</span>
              </h1>
              <p class="mt-0.5 text-sm text-gray-500">Actualiza la información y guarda los cambios.</p>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="px-6 py-6 space-y-6" novalidate>
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nombre</label>
              <input
                v-model="form.nombre"
                type="text"
                class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm placeholder:text-gray-400
                       focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">{{ form.errors.nombre }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm placeholder:text-gray-400
                       focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Teléfono</label>
              <input
                v-model="form.telefono"
                type="tel"
                class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm placeholder:text-gray-400
                       focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.telefono" class="mt-1 text-sm text-red-600">{{ form.errors.telefono }}</p>
            </div>


            <!-- Identificación opcional -->
            <!--
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700">Identificación (RUC/NIT)</label>
              <input
                v-model="form.identificacion"
                type="text"
                class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm placeholder:text-gray-400
                       focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.identificacion" class="mt-1 text-sm text-red-600">{{ form.errors.identificacion }}</p>
            </div>
            -->
          </div>

          <!-- Acciones -->
          <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
            <Link
              :href="route('vendors.index')"
              class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
            >
              Cancelar
            </Link>
            <button
              type="submit"
              :disabled="form.processing || !v.id_vendedor"
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
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
