<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

const props = defineProps({
  product: {
    type: Object,
    default: () => ({
      id_producto: null,
      nombre: '',
      descripcion: '',
      precio: 0,
      stock: 0,
      categoria: '',
      sku: '',
      activo: true,
    })
  }
})

const p = computed(() => ({
  id_producto: props.product?.id_producto ?? null,
  nombre: props.product?.nombre ?? '',
  descripcion: props.product?.descripcion ?? '',
  precio: props.product?.precio ?? 0,
  stock: props.product?.stock ?? 0,
  categoria: props.product?.categoria ?? '',
  sku: props.product?.sku ?? '',
  activo: !!props.product?.activo,
}))

const form = useForm({
  nombre: p.value.nombre,
  descripcion: p.value.descripcion,
  precio: p.value.precio,
  stock: p.value.stock,
  categoria: p.value.categoria,
  sku: p.value.sku,
})

const toast = (title = 'Hecho', icon = 'success') =>
  Swal.fire({ title, icon, toast: true, position: 'top-end', timer: 2200, showConfirmButton: false })

const submit = () => {
  Swal.fire({ title: 'Guardando cambios...', allowOutsideClick: false, didOpen: () => Swal.showLoading() })
  form.put(route('products.update', { product: p.value.id_producto }), {
    onSuccess: () => {
      Swal.close()
      toast('Producto actualizado')
    },
    onError: () => {
      Swal.fire('Revisa el formulario', 'Hay campos inválidos que corregir.', 'error')
    },
    onFinish: () => {
      if (Swal.isLoading()) Swal.close()
    },
  })
}

/** Monograma */
const initial = computed(() => (p.value.nombre || '?').toString().trim().charAt(0).toUpperCase())
</script>

<template>
  <Head title="Editar Producto" />
  <AuthenticatedLayout>
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <!-- Header -->
        <div class="border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-white px-6 py-5">
          <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-full bg-indigo-600 text-white flex items-center justify-center font-semibold">
              {{ initial }}
            </div>
            <div class="min-w-0">
              <h1 class="text-lg sm:text-xl font-semibold text-gray-900">
                Editar producto
                <span class="text-gray-400 font-normal">#{{ p.id_producto }}</span>
              </h1>
              <p class="mt-0.5 text-sm text-gray-500">Actualiza los datos y guarda cambios.</p>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="px-6 py-6 space-y-6" novalidate>
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700">Nombre</label>
              <input v-model="form.nombre" type="text" class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">{{ form.errors.nombre }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">SKU</label>
              <input v-model="form.sku" type="text" class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <p v-if="form.errors.sku" class="mt-1 text-sm text-red-600">{{ form.errors.sku }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Categoría</label>
              <input v-model="form.categoria" type="text" class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <p v-if="form.errors.categoria" class="mt-1 text-sm text-red-600">{{ form.errors.categoria }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Precio</label>
              <input v-model.number="form.precio" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <p v-if="form.errors.precio" class="mt-1 text-sm text-red-600">{{ form.errors.precio }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Stock</label>
              <input v-model.number="form.stock" type="number" step="1" min="0" class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <p v-if="form.errors.stock" class="mt-1 text-sm text-red-600">{{ form.errors.stock }}</p>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700">Descripción</label>
              <textarea v-model="form.descripcion" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 bg-white/80 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
              <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">{{ form.errors.descripcion }}</p>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
            <Link :href="route('products.index')" class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancelar</Link>
            <button type="submit" :disabled="form.processing || !p.id_producto" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-60">
              Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
