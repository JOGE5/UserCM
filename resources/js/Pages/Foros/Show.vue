<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ foro: Object, currentUserId: Number, canEdit: Boolean });

const form = useForm({ texto: '' });
const submitting = ref(false);
const alertMessage = ref('');

function submitComentario() {
  if (!form.texto) return;
  submitting.value = true;
  alertMessage.value = '';
  form.post(route('productos.comentarios.store', props.foro.ID_Foro), {
    onFinish: () => {
      submitting.value = false;
    },
    onSuccess: () => {
      form.reset('texto');
      // simple reload to show new comment
      location.reload();
    },
    onError: (errors) => {
      submitting.value = false;
      if (errors && errors.texto) {
      alertMessage.value = errors.texto[0] || 'ÑO,ÑO,ÑO, ese lenguaje aqui no >:(';
      } else if (errors && Object.keys(errors).length > 0) {
        // show first validation error
        const first = errors[Object.keys(errors)[0]];
        alertMessage.value = Array.isArray(first) ? first[0] : first;
      } else {
        alertMessage.value = 'Error al enviar el comentario.';
      }
      // auto-clear after 6s
      setTimeout(() => { alertMessage.value = ''; }, 6000);
    }
  });
}

const showDeleteModal = ref(false);
const deleteError = ref('');

function openDeleteModal() {
  showDeleteModal.value = true;
}

function closeDeleteModal() {
  showDeleteModal.value = false;
  deleteError.value = '';
}

function confirmDelete() {
  const url = route('productos.destroy', props.foro.ID_Foro);
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': token || '',
    },
    credentials: 'same-origin',
    body: JSON.stringify({ _method: 'DELETE' })
  }).then(resp => {
    if (resp.ok) {
      window.location.href = route('productos');
    } else {
      resp.json().then(j => {
        deleteError.value = j.message || 'Error al eliminar';
      });
    }
  }).catch(() => {
    deleteError.value = 'Error al eliminar';
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

          <div v-if="canEdit" class="mb-4 flex gap-2">
            <a :href="route('productos.edit', foro.ID_Foro)" class="px-3 py-1 bg-yellow-400 text-black rounded">Editar</a>
            <button @click.prevent="openDeleteModal" class="px-3 py-1 bg-red-600 text-white rounded">Eliminar</button>
          </div>

          <!-- Modal de confirmación -->
          <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
              <h3 class="text-lg font-semibold mb-2">¿Eliminar este foro?</h3>
              <p class="mb-4">Esta acción no se puede deshacer. ¿Estás seguro que quieres eliminar este foro?</p>
              <div v-if="deleteError" class="text-sm text-red-600 mb-2">{{ deleteError }}</div>
              <div class="flex justify-end space-x-2 mt-4">
                <button @click="closeDeleteModal" class="px-4 py-2 bg-gray-300 rounded">No</button>
                <button @click="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded">Sí, eliminar</button>
              </div>
            </div>
          </div>

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
              <div v-if="alertMessage" class="mb-2 p-2 bg-red-100 text-red-800 rounded">
                {{ alertMessage }}
              </div>
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
