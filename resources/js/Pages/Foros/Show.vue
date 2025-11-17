<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ foro: Object });

const form = useForm({ texto: '' });
const submitting = ref(false);

function submitComentario() {
  if (!form.texto) return;
  submitting.value = true;
  form.post(route('productos.comentarios.store', props.foro.ID_Foro || props.foro.id), {
    onFinish: () => {
      submitting.value = false;
      form.reset('texto');
      // simple reload to show new comment
      location.reload();
    }
  });
}
</script>

<template>
  <AppLayout :title="foro.Titulo_Foro">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ foro.Titulo_Foro }}</h2>
    </template>

    <div class="py-6">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded shadow">
          <div v-if="foro.Imagen_Foro" class="mb-4">
            <img :src="route('files.foros', foro.Imagen_Foro.split('/').pop())" class="w-full h-64 object-cover rounded" />
          </div>

          <div class="mb-4">
            <p class="text-gray-700" v-html="foro.Descripcion_Foro"></p>
          </div>

          <div class="text-sm text-gray-500 mb-4">Creado por: {{ foro.creador ? foro.creador.name : 'Desconocido' }}</div>

          <hr class="my-4" />

          <div>
            <h3 class="font-semibold mb-2">Comentarios</h3>

            <div v-if="foro.comentarios && foro.comentarios.length > 0" class="space-y-3 mb-4">
              <div v-for="c in foro.comentarios" :key="c.id" class="p-3 bg-gray-50 rounded">
                <div class="text-sm text-gray-700">{{ c.texto }}</div>
                <div class="text-xs text-gray-400 mt-1">Por: {{ c.usuario ? c.usuario.name : 'Desconocido' }} · {{ new Date(c.created_at).toLocaleString() }}</div>
              </div>
            </div>

            <div class="mt-4">
              <h4 class="text-sm font-medium mb-1">Agregar un comentario</h4>
              <textarea v-model="form.texto" class="input w-full" rows="3" placeholder="Escribe tu comentario..."></textarea>
              <div class="mt-2 flex items-center space-x-2">
                <button @click.prevent="submitComentario" :disabled="submitting || !form.texto" class="px-4 py-2 bg-blue-600 text-white rounded">Enviar</button>
                <button @click.prevent="form.reset('texto')" class="px-3 py-2 border rounded">Cancelar</button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>
