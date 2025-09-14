<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
})
</script>

<template>
  <Head title="Bienvenido - Maxco" />

  <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-gray-100 via-gray-200 to-gray-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    <div class="max-w-lg w-full p-8 bg-white rounded-2xl shadow-xl dark:bg-gray-800 text-center transform transition hover:scale-[1.01] hover:shadow-2xl">
      
      <!-- Header -->
      <h1 class="text-4xl font-extrabold text-gray-800 dark:text-white tracking-tight">
        🚀 Bienvenido a <span class="text-red-600">Maxco</span>
      </h1>
      <p class="mt-4 text-base text-gray-600 dark:text-gray-300 leading-relaxed">
        Tu plataforma de gestión y análisis en un solo lugar. 
        Ingresa y comienza a optimizar tus procesos con <span class="font-semibold text-red-600">Maxco</span>.
      </p>

      <!-- Botones -->
      <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
        
        <!-- Si ya está autenticado -->
        <Link
          v-if="$page.props.auth.user"
          :href="route('dashboard')"
          class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold shadow-md hover:from-red-600 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition"
        >
          Ir al Dashboard
        </Link>

        <!-- Si no está autenticado -->
        <template v-else>
          <Link
            v-if="canLogin"
            :href="route('login')"
            class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-gray-800 to-gray-900 text-white font-semibold shadow-md hover:from-black hover:to-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-700 transition"
          >
            Iniciar Sesión
          </Link>

          <Link
            v-if="canRegister"
            :href="route('register')"
            class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold shadow-md hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition"
          >
            Registrarse
          </Link>
        </template>
      </div>

      <!-- Footer -->
      <div class="mt-8 text-xs text-gray-500 dark:text-gray-400">
        <p>© {{ new Date().getFullYear() }} Maxco • Todos los derechos reservados</p>
      </div>
    </div>
  </div>
</template>
