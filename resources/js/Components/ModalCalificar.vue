<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { Star, X } from 'lucide-vue-next';

const props = defineProps({
  userId:   { type: Number, required: true },
  userName: { type: String, default: 'este usuario' },
});

const emit = defineEmits(['close', 'success']);

const puntuacion  = ref(0);
const hovered     = ref(0);
const comentario  = ref('');
const loading     = ref(false);
const errorMsg    = ref(null);

async function submit() {
  if (puntuacion.value < 1) { errorMsg.value = 'Selecciona al menos 1 estrella.'; return; }
  loading.value = true;
  errorMsg.value = null;
  try {
    const { data } = await axios.post(`/api/reputacion/${props.userId}`, {
      Puntuacion: puntuacion.value,
      Comentario: comentario.value || null,
    });
    emit('success', data);
  } catch (e) {
    errorMsg.value = e.response?.data?.error || 'Error al enviar la calificación. Intenta de nuevo.';
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="emit('close')"></div>

      <!-- Panel -->
      <div class="relative z-10 w-full max-w-md bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2.5rem] shadow-2xl p-8">
        <!-- Header -->
        <div class="flex items-start justify-between mb-6">
          <div>
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Calificar a {{ userName }}</h3>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Tu opinión mejora el marketplace</p>
          </div>
          <button @click="emit('close')" class="p-2 rounded-xl text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-all">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Estrellas -->
        <div class="mb-6">
          <p class="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-3">Puntuación</p>
          <div class="flex gap-2">
            <button
              v-for="i in 5"
              :key="i"
              @mouseenter="hovered = i"
              @mouseleave="hovered = 0"
              @click="puntuacion = i"
              class="transition-transform hover:scale-110 active:scale-95"
            >
              <Star
                class="w-9 h-9 transition-colors"
                :class="i <= (hovered || puntuacion)
                  ? 'text-amber-400 fill-amber-400'
                  : 'text-gray-200 dark:text-gray-700 fill-gray-200 dark:fill-gray-700'"
              />
            </button>
          </div>
          <p class="text-[10px] font-bold text-gray-400 mt-2">
            {{ ['', 'Muy malo', 'Malo', 'Regular', 'Bueno', 'Excelente'][puntuacion] || 'Toca una estrella' }}
          </p>
        </div>

        <!-- Comentario -->
        <div class="mb-6">
          <label class="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Comentario (opcional)</label>
          <textarea
            v-model="comentario"
            rows="3"
            maxlength="500"
            placeholder="Comparte tu experiencia con este vendedor..."
            class="mt-2 w-full px-4 py-3 bg-gray-50/50 dark:bg-white/5 border border-light-border dark:border-dark-border text-gray-900 dark:text-white rounded-2xl focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all text-sm outline-none resize-none font-medium"
          ></textarea>
        </div>

        <!-- Error -->
        <p v-if="errorMsg" class="text-xs font-bold text-rose-500 mb-4">{{ errorMsg }}</p>

        <!-- Botones -->
        <div class="flex gap-3">
          <button
            @click="emit('close')"
            class="flex-1 py-4 text-xs font-black tracking-widest uppercase border border-light-border dark:border-dark-border text-gray-600 dark:text-gray-400 rounded-2xl hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
          >
            Cancelar
          </button>
          <button
            @click="submit"
            :disabled="loading || puntuacion < 1"
            class="flex-1 py-4 text-xs font-black tracking-widest uppercase bg-brand-600 hover:bg-brand-500 text-white rounded-2xl shadow-lg shadow-brand-500/20 transition-all hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="animate-pulse">Enviando...</span>
            <span v-else>Enviar calificación</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
