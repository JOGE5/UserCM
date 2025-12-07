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
  <div class="p-4">
    <h3 class="font-semibold mb-2">Reportar publicación</h3>
    <div class="mb-2">
      <label class="block text-sm">Motivo</label>
      <select v-model="form.reason" class="mt-1 border rounded w-full px-2 py-1">
        <option value="">Selecciona un motivo</option>
        <option>Lenguaje ofensivo</option>
        <option>Imagen explícita/indecente</option>
        <option>Spam</option>
        <option>Otro</option>
      </select>
    </div>
    <div class="mb-2">
      <label class="block text-sm">Comentario (opcional)</label>
      <textarea v-model="form.comment" rows="3" class="mt-1 border rounded w-full px-2 py-1" placeholder="Agrega contexto o ejemplos"></textarea>
    </div>
    <div class="mb-2">
      <label class="inline-flex items-center">
        <input type="checkbox" v-model="form.flags.image" class="mr-2" /> Contiene imagen explícita
      </label>
    </div>

    <div v-if="detected" class="mb-2 text-yellow-700">Se ha detectado lenguaje potencialmente ofensivo en tu comentario.</div>

    <div class="flex gap-2 mt-3">
      <button class="px-3 py-1 bg-red-600 text-white rounded" @click.prevent="submit">Enviar reporte</button>
      <button class="px-3 py-1 bg-gray-300 rounded" @click.prevent="close">Cancelar</button>
    </div>
  </div>
</template>

<style scoped>
</style>
