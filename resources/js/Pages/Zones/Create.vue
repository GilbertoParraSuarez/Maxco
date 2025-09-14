<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { CheckIcon } from '@heroicons/vue/24/outline'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

const form = useForm({
  nombre_zona: '',
  descripcion: ''
})

const toast = (title = 'Hecho', icon = 'success') =>
  Swal.fire({ title, icon, toast: true, position: 'top-end', timer: 2200, showConfirmButton: false })

const submit = () => {
  Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() })
  form.post(route('zones.store'), {
    onSuccess: () => { Swal.close(); toast('Zona creada') },
    onError: () => Swal.fire('Revisa el formulario', 'Hay campos inválidos que corregir.', 'error'),
    onFinish: () => { if (Swal.isLoading()) Swal.close() }
  })
}
</script>

<template>
  <Head title="Nueva Zona" />
  <AuthenticatedLayout>
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h3 class="text-lg font-semibold text-gray-900">Nueva Zona</h3>
          <p class="text-sm text-gray-600">Crea una zona para organizar áreas de ventas o logística.</p>
        </div>

        <form @submit.prevent="submit" class="px-6 py-6 space-y-6" novalidate>
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nombre de la zona</label>
              <input v-model="form.nombre_zona" type="text" class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
              <p v-if="form.errors.nombre_zona" class="mt-1 text-sm text-red-600">{{ form.errors.nombre_zona }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Descripción</label>
              <textarea v-model="form.descripcion" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
              <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">{{ form.errors.descripcion }}</p>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
            <Link :href="route('zones.index')" class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50">Cancelar</Link>
            <button :disabled="form.processing" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">
              <CheckIcon class="h-4 w-4" /> Guardar Zona
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
