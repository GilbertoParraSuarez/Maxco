<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { UserPlusIcon, ArrowLeftIcon, CheckIcon } from '@heroicons/vue/24/outline'

const form = useForm({
  nombre: '', 
  email: '', 
  telefono: '', 
  direccion: ''
})

const submit = () => form.post(route('customers.store'))
</script>

<template>
  <Head title="Nuevo Cliente" />
  <AuthenticatedLayout>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-xl sm:rounded-xl">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h3 class="text-lg font-semibold text-gray-900">Información del Cliente</h3>
          <p class="mt-1 text-sm text-gray-600">Complete todos los campos requeridos para crear el cliente.</p>
        </div>

        <form @submit.prevent="submit" class="p-6">
          <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Nombre -->
            <div class="lg:col-span-2">
              <label for="nombre" class="block text-sm font-semibold text-gray-900">
                Nombre Completo <span class="text-red-500">*</span>
              </label>
              <div class="mt-2">
                <input
                  id="nombre"
                  v-model="form.nombre"
                  type="text"
                  required
                  class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-500 shadow-sm transition-colors focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 sm:text-sm"
                  :class="{'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.nombre}"
                  placeholder="Ingrese el nombre completo del cliente"
                />
              </div>
              <div v-if="form.errors.nombre" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {{ form.errors.nombre }}
              </div>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-semibold text-gray-900">
                Correo Electrónico <span class="text-red-500">*</span>
              </label>
              <div class="mt-2">
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-500 shadow-sm transition-colors focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 sm:text-sm"
                  :class="{'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.email}"
                  placeholder="cliente@ejemplo.com"
                />
              </div>
              <div v-if="form.errors.email" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {{ form.errors.email }}
              </div>
            </div>

            <!-- Teléfono -->
            <div>
              <label for="telefono" class="block text-sm font-semibold text-gray-900">
                Teléfono
              </label>
              <div class="mt-2">
                <input
                  id="telefono"
                  v-model="form.telefono"
                  type="tel"
                  class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-500 shadow-sm transition-colors focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 sm:text-sm"
                  placeholder="+593 99 123 4567"
                />
              </div>
            </div>

            <!-- Dirección -->
            <div class="lg:col-span-2">
              <label for="direccion" class="block text-sm font-semibold text-gray-900">
                Dirección
              </label>
              <div class="mt-2">
                <textarea
                  id="direccion"
                  v-model="form.direccion"
                  rows="3"
                  class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-500 shadow-sm transition-colors focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 sm:text-sm"
                  placeholder="Ingrese la dirección completa del cliente"
                />
              </div>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="mt-8 flex flex-col-reverse gap-3 border-t border-gray-200 pt-6 sm:flex-row sm:justify-end">
            <Link 
              :href="route('customers.index')" 
              class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
              Cancelar
            </Link>
            <button
              :disabled="form.processing"
              type="submit"
              class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-6 py-3 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            >
              <CheckIcon v-if="!form.processing" class="h-4 w-4" />
              <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              {{ form.processing ? 'Guardando...' : 'Guardar Cliente' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>