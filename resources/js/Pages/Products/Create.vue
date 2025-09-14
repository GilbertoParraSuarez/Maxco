<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { CheckIcon } from '@heroicons/vue/24/outline'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

const form = useForm({
  nombre: '',
  descripcion: '',
  precio: '',
  stock: '',
  categoria: '',
  sku: '',
})

const toast = (title = 'Hecho', icon = 'success') =>
  Swal.fire({ title, icon, toast: true, position: 'top-end', timer: 2200, showConfirmButton: false })

const submit = () => {
  Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() })
  form.post(route('products.store'), {
    onSuccess: () => {
      // El index mostrará el toast por flash.success, pero mostramos uno inmediato también
      Swal.close()
      toast('Producto creado')
    },
    onError: () => {
      Swal.fire('Revisa el formulario', 'Hay campos inválidos que corregir.', 'error')
    },
    onFinish: () => {
      // Por si hubo error, cerramos cualquier loading pendiente
      if (Swal.isLoading()) Swal.close()
    },
  })
}
</script>

<template>
  <Head title="Nuevo Producto" />
  <AuthenticatedLayout>
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h3 class="text-lg font-semibold text-gray-900">Nuevo Producto</h3>
          <p class="text-sm text-gray-600">Registra un producto con su información básica.</p>
        </div>

        <form @submit.prevent="submit" class="px-6 py-6 space-y-6" novalidate>
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700">Nombre</label>
              <input v-model="form.nombre" type="text" class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
              <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">{{ form.errors.nombre }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">SKU</label>
              <input v-model="form.sku" type="text" class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
              <p v-if="form.errors.sku" class="mt-1 text-sm text-red-600">{{ form.errors.sku }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Categoría</label>
              <input v-model="form.categoria" type="text" class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
              <p v-if="form.errors.categoria" class="mt-1 text-sm text-red-600">{{ form.errors.categoria }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Precio</label>
              <input v-model="form.precio" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
              <p v-if="form.errors.precio" class="mt-1 text-sm text-red-600">{{ form.errors.precio }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Stock</label>
              <input v-model="form.stock" type="number" step="1" min="0" class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
              <p v-if="form.errors.stock" class="mt-1 text-sm text-red-600">{{ form.errors.stock }}</p>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700">Descripción</label>
              <textarea v-model="form.descripcion" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
              <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">{{ form.errors.descripcion }}</p>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
            <Link :href="route('products.index')" class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50">Cancelar</Link>
            <button :disabled="form.processing" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">
              <CheckIcon class="h-4 w-4" />
              Guardar Producto
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
