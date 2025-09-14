<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

const props = defineProps({
  zone: {
    type: Object,
    default: () => ({
      id_zona: null,
      nombre_zona: '',
      descripcion: '',
      activo: true,
    })
  }
})

const z = computed(() => ({
  id_zona: props.zone?.id_zona ?? null,
  nombre_zona: props.zone?.nombre_zona ?? '',
  descripcion: props.zone?.descripcion ?? '',
  activo: !!props.zone?.activo,
}))

const form = useForm({
  nombre_zona: z.value.nombre_zona,
  descripcion: z.value.descripcion,
})

const toast = (title = 'Hecho', icon = 'success') =>
  Swal.fire({ title, icon, toast: true, position: 'top-end', timer: 2200, showConfirmButton: false })

const submit = () => {
  Swal.fire({ title: 'Guardando cambios...', allowOutsideClick: false, didOpen: () => Swal.showLoading() })
  form.put(route('zones.update', { zone: z.value.id_zona }), {
    onSuccess: () => { Swal.close(); toast('Zona actualizada') },
    onError: () => Swal.fire('Revisa el formulario', 'Hay campos inválidos que corregir.', 'error'),
    onFinish: () => { if (Swal.isLoading()) Swal.close() }
  })
}

const initial = computed(() => (z.value.nombre_zona || '?').toString().trim().charAt(0).toUpperCase())
</script>

<template>
  <Head title="Editar Zona" />
  <AuthenticatedLayout>
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <!-- Header -->
        <div class="border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-white px-6 py-5">
          <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-full bg-indigo-600 text-white flex items-center justify-center font-semibold">
              {{ initial }}
            </div>
            <div class="min-w-0">
              <h1 class="text-lg sm:text-xl font-semibold text-gray-900">
                Editar zona <span class="text-gray-400 font-normal">#{{ z.id_zona }}</span>
              </h1>
              <p class="mt-0.5 text-sm text-gray-500">Actualiza los datos y guarda cambios.</p>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="px-6 py-6 space-y-6" novalidate>
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nombre de la zona</label>
              <input v-model="form.nombre_zona" type="text" class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <p v-if="form.errors.nombre_zona" class="mt-1 text-sm text-red-600">{{ form.errors.nombre_zona }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Descripción</label>
              <textarea v-model="form.descripcion" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
              <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">{{ form.errors.descripcion }}</p>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
            <Link :href="route('zones.index')" class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancelar</Link>
            <button type="submit" :disabled="form.processing || !z.id_zona" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-60">
              Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
