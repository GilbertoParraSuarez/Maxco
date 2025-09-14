<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: {
    type: String,
    required: true,
  },
  phpVersion: {
    type: String,
    required: true,
  },
})
</script>

<template>
  <Head title="Bienvenido" />

  <div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="max-w-lg p-8 bg-white rounded-2xl shadow-lg dark:bg-gray-800 text-center">
      <h1 class="text-3xl font-bold text-gray-800 dark:text-white">🚀 Bienvenido a tu Panel</h1>
      <p class="mt-4 text-gray-600 dark:text-gray-300">
        Este es tu punto de inicio con <span class="font-semibold text-red-600">Laravel {{ laravelVersion }}</span>
        y PHP <span class="font-semibold">{{ phpVersion }}</span>.
      </p>

      <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
        <Link
          v-if="$page.props.auth.user"
          :href="route('dashboard')"
          class="px-5 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition"
        >
          Ir al Dashboard
        </Link>

        <template v-else>
          <Link
            v-if="canLogin"
            :href="route('login')"
            class="px-5 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-900 transition"
          >
            Iniciar Sesión
          </Link>

          <Link
            v-if="canRegister"
            :href="route('register')"
            class="px-5 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition"
          >
            Registrarse
          </Link>
        </template>
      </div>
    </div>
  </div>
</template>
