<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { PlusIcon, TrashIcon, CheckIcon } from '@heroicons/vue/24/outline'
import { computed } from 'vue'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

const props = defineProps({
  customers: Array,
  vendors: Array,
  zones: Array,
  products: Array,
})

const form = useForm({
  id_cliente: '',
  id_vendedor: '',
  id_zona: '',
  numero_documento: '',
  metodo_pago: 'EFECTIVO',
  observaciones: '',
  detalles: []
})

/** Solo informativo para el usuario (la que ve en su navegador) */
const fechaLocal = computed(() =>
  new Date().toLocaleString('es-EC', { dateStyle: 'medium', timeStyle: 'short' })
)

/** Mapa de productos */
const productMap = computed(() => {
  const map = {}
  if (props.products && Array.isArray(props.products)) {
    for (const p of props.products) {
      if (p && p.id_producto) {
        map[p.id_producto] = p
      }
    }
  }
  return map
})

/** Líneas */
const addLine = () => {
  form.detalles.push({
    id_producto: '',
    cantidad: 1,
    precio_unitario: 0,
    descuento: 0,
    impuesto: 15,
    unidad: 'UND'
  })
}

const removeLine = (idx) => {
  if (idx >= 0 && idx < form.detalles.length) {
    form.detalles.splice(idx, 1)
  }
}

/** Elegir producto: precio fijo + validar stock */
const onPickProduct = (line) => {
  if (!line || !line.id_producto) return
  
  const p = productMap.value[line.id_producto]
  if (!p) {
    line.precio_unitario = 0
    return
  }

  const stock = Number(p.stock) || 0
  const precio = Number(p.precio) || 0

  if (stock <= 0) {
    Swal.fire('Sin stock', `El producto "${p.nombre}" no tiene stock disponible.`, 'warning')
    line.id_producto = ''
    line.precio_unitario = 0
    return
  }

  line.precio_unitario = precio
  if (!line.unidad) line.unidad = 'UND'

  const cantidad = Number(line.cantidad) || 1
  if (cantidad > stock) {
    line.cantidad = stock
    Swal.fire('Stock limitado', `Stock disponible: ${stock} unidades.`, 'info')
  }
}

/** Cantidad dentro del stock */
const onQuantityInput = (line) => {
  if (!line || !line.id_producto) return
  
  const p = productMap.value[line.id_producto]
  if (!p) return
  
  const maxStock = Number(p.stock) || 0
  const cantidad = Number(line.cantidad) || 0
  
  if (cantidad <= 0) {
    line.cantidad = 1
  } else if (cantidad > maxStock) {
    line.cantidad = maxStock
    Swal.fire('Stock insuficiente', `Stock máximo disponible: ${maxStock} unidades.`, 'warning')
  }
}

/** Cálculos */
const calcLine = (l) => {
  try {
    const qty = Number(l.cantidad) || 0
    const price = Number(l.precio_unitario) || 0
    const disc = Number(l.descuento) || 0
    const impPct = Number(l.impuesto) || 0
    
    const subtotal = qty * price
    const descuento = Math.min(Math.abs(disc), subtotal)
    const base = Math.max(0, subtotal - descuento)
    const impuesto = Number((base * (impPct / 100)).toFixed(2))
    const total = Number((base + impuesto).toFixed(2))
    
    return { 
      subtotal: Number(subtotal.toFixed(2)), 
      descuento: Number(descuento.toFixed(2)), 
      impuesto, 
      total 
    }
  } catch (error) {
    console.error('Error en cálculo de línea:', error)
    return { subtotal: 0, descuento: 0, impuesto: 0, total: 0 }
  }
}

const resumen = computed(() => {
  try {
    let sub = 0, desc = 0, imp = 0, tot = 0
    
    if (Array.isArray(form.detalles)) {
      for (const l of form.detalles) {
        if (l && l.id_producto) {
          const r = calcLine(l)
          sub += r.subtotal
          desc += r.descuento
          imp += r.impuesto
          tot += r.total
        }
      }
    }
    
    return {
      subtotal: Number(sub.toFixed(2)),
      descuento: Number(desc.toFixed(2)),
      impuesto: Number(imp.toFixed(2)),
      total: Number(tot.toFixed(2))
    }
  } catch (error) {
    console.error('Error en resumen:', error)
    return { subtotal: 0, descuento: 0, impuesto: 0, total: 0 }
  }
})

const money = (n) => {
  try {
    return new Intl.NumberFormat('es-EC', { 
      style: 'currency', 
      currency: 'USD' 
    }).format(Number(n) || 0)
  } catch (error) {
    return `$${(Number(n) || 0).toFixed(2)}`
  }
}

/** Validación final mejorada */
const validateLines = () => {
  try {
    if (!Array.isArray(form.detalles) || form.detalles.length === 0) {
      Swal.fire('Sin productos', 'Agrega al menos un producto a la venta.', 'warning')
      return false
    }

    for (const [i, l] of form.detalles.entries()) {
      if (!l || !l.id_producto) {
        Swal.fire('Producto requerido', `Selecciona un producto en la línea ${i + 1}.`, 'warning')
        return false
      }

      const p = productMap.value[l.id_producto]
      if (!p) {
        Swal.fire('Producto no encontrado', `El producto en la línea ${i + 1} no existe.`, 'error')
        return false
      }

      const stock = Number(p.stock) || 0
      const cantidad = Number(l.cantidad) || 0

      if (stock <= 0) {
        Swal.fire('Sin stock', `El producto "${p.nombre}" no tiene stock disponible.`, 'error')
        return false
      }

      if (cantidad <= 0) {
        Swal.fire('Cantidad inválida', `La cantidad debe ser mayor a 0 en la línea ${i + 1}.`, 'warning')
        return false
      }

      if (cantidad > stock) {
        Swal.fire('Stock insuficiente', `Stock disponible de "${p.nombre}": ${stock} unidades.`, 'warning')
        return false
      }

      // Asegurar precio correcto
      l.precio_unitario = Number(p.precio) || 0
      
      // Validar descuento
      const subtotalLinea = cantidad * l.precio_unitario
      if (Number(l.descuento) > subtotalLinea) {
        l.descuento = subtotalLinea
      }
    }

    return true
  } catch (error) {
    console.error('Error en validación:', error)
    Swal.fire('Error de validación', 'Ocurrió un error al validar los datos.', 'error')
    return false
  }
}

const submit = () => {
  if (!validateLines()) return

  // Validaciones básicas del formulario
  if (!form.id_cliente) {
    Swal.fire('Cliente requerido', 'Selecciona un cliente.', 'warning')
    return
  }
  
  if (!form.id_vendedor) {
    Swal.fire('Vendedor requerido', 'Selecciona un vendedor.', 'warning')
    return
  }
  
  if (!form.id_zona) {
    Swal.fire('Zona requerida', 'Selecciona una zona.', 'warning')
    return
  }

  // Preparar datos para envío
  const formData = {
    id_cliente: Number(form.id_cliente),
    id_vendedor: Number(form.id_vendedor),
    id_zona: Number(form.id_zona),
    numero_documento: form.numero_documento || '',
    metodo_pago: form.metodo_pago || 'EFECTIVO',
    observaciones: form.observaciones || '',
    detalles: form.detalles.map(l => ({
      id_producto: Number(l.id_producto),
      cantidad: Number(l.cantidad),
      precio_unitario: Number(l.precio_unitario),
      descuento: Number(l.descuento) || 0,
      impuesto: Number(l.impuesto) || 0,
      unidad: l.unidad || 'UND'
    }))
  }

  console.log('Datos a enviar:', formData) // Para debug

  Swal.fire({ 
    title: 'Guardando venta...', 
    allowOutsideClick: false, 
    didOpen: () => Swal.showLoading() 
  })

  form.transform(() => formData).post(route('sales.store'), {
    preserveScroll: true,
    onSuccess: (page) => {
      Swal.close()
      Swal.fire({ 
        icon: 'success', 
        title: 'Venta registrada correctamente', 
        timer: 2000, 
        showConfirmButton: false 
      })
    },
    onError: (errors) => {
      console.error('Errores del servidor:', errors)
      Swal.close()
      
      // Mostrar errores específicos
      const errorMessages = Object.values(errors).flat()
      const errorText = errorMessages.length > 0 
        ? errorMessages.join('\n') 
        : 'Revisa los datos del formulario.'
      
      Swal.fire('Error al guardar', errorText, 'error')
    },
    onFinish: () => {
      if (Swal.isLoading()) {
        Swal.close()
      }
    }
  })
}

// Inicializar con una línea si no hay detalles
if (!Array.isArray(form.detalles) || form.detalles.length === 0) {
  addLine()
}
</script>

<template>
  <Head title="Nueva Venta" />
  <AuthenticatedLayout>
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h3 class="text-lg font-semibold text-gray-900">Registrar Nueva Venta</h3>
          <p class="text-sm text-gray-600">Completa los datos y añade productos. Los totales se calculan automáticamente.</p>
        </div>

        <form @submit.prevent="submit" class="px-6 py-6 space-y-8" novalidate>
          <!-- Encabezado -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div>
              <label class="block text-sm font-medium text-gray-700">Cliente *</label>
              <select 
                v-model="form.id_cliente" 
                class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                required
              >
                <option value="">Seleccione un cliente...</option>
                <option v-for="c in customers" :key="c.id_cliente" :value="c.id_cliente">
                  {{ c.nombre }}
                </option>
              </select>
              <p v-if="form.errors.id_cliente" class="mt-1 text-sm text-red-600">{{ form.errors.id_cliente }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Vendedor *</label>
              <select 
                v-model="form.id_vendedor" 
                class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                required
              >
                <option value="">Seleccione un vendedor...</option>
                <option v-for="v in vendors" :key="v.id_vendedor" :value="v.id_vendedor">
                  {{ v.nombre }}
                </option>
              </select>
              <p v-if="form.errors.id_vendedor" class="mt-1 text-sm text-red-600">{{ form.errors.id_vendedor }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Zona *</label>
              <select 
                v-model="form.id_zona" 
                class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                required
              >
                <option value="">Seleccione una zona...</option>
                <option v-for="z in zones" :key="z.id_zona" :value="z.id_zona">
                  {{ z.nombre_zona }}
                </option>
              </select>
              <p v-if="form.errors.id_zona" class="mt-1 text-sm text-red-600">{{ form.errors.id_zona }}</p>
            </div>

            <!-- Fecha automática -->
            <div class="md:col-span-3">
              <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-200">
                Fecha y hora: automático (servidor)
              </span>
              <span class="ml-2 text-xs text-gray-500">Tu hora local: {{ fechaLocal }}</span>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Método de pago</label>
              <select v-model="form.metodo_pago" class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="EFECTIVO">Efectivo</option>
                <option value="TARJETA">Tarjeta</option>
                <option value="TRANSFERENCIA">Transferencia</option>
              </select>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700">Nº Documento</label>
              <input 
                v-model="form.numero_documento" 
                type="text" 
                class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" 
                placeholder="Número de factura o comprobante (opcional)"
              />
              <p class="mt-1 text-xs text-gray-500">
                Si lo dejas vacío, se generará automáticamente. Debe ser único por vendedor.
              </p>
              <p v-if="form.errors.numero_documento" class="mt-1 text-sm text-red-600">{{ form.errors.numero_documento }}</p>
            </div>

            <div class="md:col-span-3">
              <label class="block text-sm font-medium text-gray-700">Observaciones</label>
              <textarea 
                v-model="form.observaciones" 
                rows="2" 
                class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Comentarios adicionales..."
              ></textarea>
            </div>
          </div>

          <!-- Detalle de productos -->
          <div class="overflow-hidden rounded-xl border border-gray-200">
            <div class="flex items-center justify-between bg-gray-50 px-4 py-3">
              <h4 class="text-sm font-semibold text-gray-900">Detalle de productos</h4>
              <button 
                type="button" 
                @click="addLine" 
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
              >
                <PlusIcon class="h-4 w-4" /> Agregar producto
              </button>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cantidad</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Precio Unit.</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Descuento</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Imp. %</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Subtotal</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="(l, idx) in form.detalles" :key="idx">
                    <td class="px-4 py-3">
                      <select 
                        v-model="l.id_producto" 
                        @change="onPickProduct(l)" 
                        class="block w-64 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                      >
                        <option value="">Seleccione producto...</option>
                        <option 
                          v-for="p in products" 
                          :key="p.id_producto" 
                          :value="p.id_producto" 
                          :disabled="!p.stock || p.stock <= 0"
                        >
                          {{ p.nombre }} 
                          <span v-if="p.sku">({{ p.sku }})</span>
                          - ${{ Number(p.precio || 0).toFixed(2) }}
                          <span v-if="p.stock > 0"> — Stock: {{ p.stock }}</span>
                          <span v-else> — Sin stock</span>
                        </option>
                      </select>
                    </td>
                    <td class="px-4 py-3">
                      <input
                        v-model.number="l.cantidad"
                        @input="onQuantityInput(l)"
                        type="number"
                        step="1" 
                        min="1"
                        :max="productMap[l.id_producto]?.stock || 999"
                        class="block w-20 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                      />
                    </td>
                    <td class="px-4 py-3">
                      <input
                        :value="money(l.precio_unitario || 0)"
                        type="text"
                        readonly
                        disabled
                        class="block w-24 rounded-lg border-gray-200 bg-gray-50 text-gray-700"
                      />
                    </td>
                    <td class="px-4 py-3">
                      <input 
                        v-model.number="l.descuento" 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        :max="(Number(l.cantidad) || 0) * (Number(l.precio_unitario) || 0)"
                        class="block w-20 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" 
                      />
                    </td>
                    <td class="px-4 py-3">
                      <input 
                        v-model.number="l.impuesto" 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        max="100"
                        class="block w-16 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" 
                      />
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700">
                      {{ money(calcLine(l).subtotal) }}
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                      {{ money(calcLine(l).total) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                      <button 
                        type="button" 
                        @click="removeLine(idx)" 
                        class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-2 py-1.5 text-sm font-medium text-red-700 hover:bg-red-100"
                      >
                        <TrashIcon class="h-4 w-4" />
                      </button>
                    </td>
                  </tr>

                  <tr v-if="form.detalles.length === 0">
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                      No hay productos agregados. Haz clic en "Agregar producto" para comenzar.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Resumen de totales -->
          <div class="grid md:grid-cols-2 gap-6">
            <div class="text-sm text-gray-600 space-y-1">
              <p>• El precio se toma automáticamente del producto</p>
              <p>• El impuesto se calcula sobre el subtotal menos descuento</p>
              <p>• El descuento no puede superar el subtotal de la línea</p>
              <p>• Moneda: USD (Dólares estadounidenses)</p>
            </div>
            <div class="rounded-xl border border-gray-200 p-4 bg-gray-50">
              <dl class="space-y-2">
                <div class="flex justify-between">
                  <dt class="text-sm text-gray-600">Subtotal:</dt>
                  <dd class="text-sm font-medium text-gray-900">{{ money(resumen.subtotal) }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-gray-600">Descuentos:</dt>
                  <dd class="text-sm font-medium text-red-600">- {{ money(resumen.descuento) }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-gray-600">Impuestos:</dt>
                  <dd class="text-sm font-medium text-gray-900">{{ money(resumen.impuesto) }}</dd>
                </div>
                <div class="mt-3 border-t border-gray-300 pt-3 flex justify-between">
                  <dt class="text-base font-semibold text-gray-900">Total:</dt>
                  <dd class="text-base font-bold text-indigo-700">{{ money(resumen.total) }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
            <Link 
              :href="route('sales.index')" 
              class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50"
            >
              Cancelar
            </Link>
            <button 
              type="submit"
              :disabled="form.processing || form.detalles.length === 0" 
              class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <CheckIcon class="h-4 w-4" /> 
              {{ form.processing ? 'Guardando...' : 'Guardar Venta' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>