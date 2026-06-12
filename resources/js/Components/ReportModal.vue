<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  publicacionId: { type: [String, Number], required: true },
  ownerId: { type: [String, Number], required: false },
});

const emit = defineEmits(['close']);

const form = useForm({ reason: '', comment: '', flags: {}, metadata: {} });
const profanities = ref([]);
const detected = ref(false);

// Cargar lista de palabrotas
fetch('/api/profanities').then(r => r.json()).then(data => { profanities.value = data || []; }).catch(() => {});

// simple detector: chequea palabras completas
function checkProfanity(text) {
  if (!text) return false;
  const clean = text.toLowerCase().normalize('NFD').replace(/[^a-z0-9\s]/g, ' ');
  const words = clean.split(/\s+/).filter(Boolean);
  for (const w of words) {
    if (profanities.value.includes(w)) return true;
  }
  return false;
}

watch(() => form.comment, (val) => {
  detected.value = checkProfanity(val);
  if (detected.value) {
    // autocompletar motivo si se detecta
    form.reason = 'Lenguaje ofensivo';
    form.metadata = Object.assign({}, form.metadata, { detected_words: true });
  }
});

function submit() {
  // si detectó palabrotas, marcar metadata
  if (detected.value) {
    form.metadata = Object.assign({}, form.metadata, { auto_detected: true });
  }
  // incluir flags como metadata
  form.metadata = Object.assign({}, form.metadata, { flags: form.flags, comment: form.comment });
  form.post(route('report.publicacion', props.publicacionId), {
    onSuccess: () => {
      alert('Reporte enviado. Gracias.');
      emit('close');
    },
    onError: () => {
      alert('Error al enviar reporte.');
    }
  });
}

function close() { emit('close'); }
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div class="fixed inset-0 z-[100] flex items-center justify-center px-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="close"></div>
        
        <!-- Panel -->
        <div class="relative z-10 w-full max-w-md bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2.5rem] shadow-2xl p-8">
          <h3 class="font-black text-xl mb-6 text-gray-900 dark:text-white">Reportar publicación</h3>
          
          <div class="mb-5">
            <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-2">Motivo</label>
            <select v-model="form.reason" class="w-full bg-gray-100 dark:bg-black/40 border border-light-border dark:border-dark-border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-rose-500/50 dark:text-white transition-all">
              <option value="">Selecciona un motivo</option>
              <option>Lenguaje ofensivo</option>
              <option>Imagen explícita/indecente</option>
              <option>Spam</option>
              <option>Otro</option>
            </select>
          </div>
          
          <div class="mb-5">
            <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-2">Comentario (opcional)</label>
            <textarea v-model="form.comment" rows="3" class="w-full bg-gray-100 dark:bg-black/40 border border-light-border dark:border-dark-border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-rose-500/50 dark:text-white transition-all resize-none" placeholder="Agrega contexto o ejemplos"></textarea>
          </div>
          
          <div class="mb-5">
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="checkbox" v-model="form.flags.image" class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 dark:bg-dark-surface text-rose-500 focus:ring-rose-500" />
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Contiene imagen explícita</span>
            </label>
          </div>

          <div v-if="detected" class="mb-6 p-4 bg-amber-500/10 border border-amber-500/30 rounded-2xl text-xs font-bold text-amber-700 dark:text-amber-400">
            Se ha detectado lenguaje potencialmente ofensivo en tu comentario.
          </div>

          <div class="flex gap-3">
            <button class="flex-1 py-4 text-xs font-black tracking-widest uppercase border border-light-border dark:border-dark-border text-gray-600 dark:text-gray-400 rounded-2xl hover:bg-gray-50 dark:hover:bg-white/5 transition-all" @click.prevent="close">
              Cancelar
            </button>
            <button class="flex-1 py-4 text-xs font-black tracking-widest uppercase bg-rose-600 hover:bg-rose-500 text-white rounded-2xl shadow-lg shadow-rose-500/20 transition-all active:scale-95 disabled:opacity-50" @click.prevent="submit" :disabled="form.processing">
              Enviar reporte
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
