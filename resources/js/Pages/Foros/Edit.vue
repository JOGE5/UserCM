<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';

const props = defineProps({ foro: Object, categorias: Array });

const form = useForm({
  Titulo_Foro: props.foro.Titulo_Foro || '',
  Descripcion_Foro: props.foro.Descripcion_Foro || '',
  Cod_Categoria: props.foro.Cod_Categoria || null,
  Imagen_Foro: null,
  _method: 'PUT',
});

const imagePreview = ref(props.foro.Imagen_Foro ? route('files.foros', props.foro.Imagen_Foro.split('/').pop()) : null);

function handleImage(e) {
  const file = e.target.files[0];
  if (!file) return;
  if (!file.type.startsWith('image/')) {
    alert('Solo imágenes');
    return;
  }
  if (file.size > 2 * 1024 * 1024) {
    alert('Imagen demasiado grande (máx 2MB)');
    return;
  }
  form.Imagen_Foro = file;
  const reader = new FileReader();
  reader.onload = () => imagePreview.value = reader.result;
  reader.readAsDataURL(file);
}

function submit() {
  form.post(route('productos.update', props.foro.id));
}
</script>

<template>
  <AppLayout title="Editar Foro">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Foro</h2>
    </template>

    <div class="py-6">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded shadow">
          <div class="mb-4">
            <label class="block">Título</label>
            <input v-model="form.Titulo_Foro" class="input" />
            <InputError :message="form.errors.Titulo_Foro" />
          </div>

          <div class="mb-4">
            <label class="block">Descripción</label>
            <textarea v-model="form.Descripcion_Foro" class="input"></textarea>
            <InputError :message="form.errors.Descripcion_Foro" />
          </div>

          <div class="mb-4">
            <label class="block">Categoría</label>
            <select v-model="form.Cod_Categoria" class="input">
              <option value="" disabled>Selecciona una...</option>
              <option v-for="c in categorias" :key="c.Cod_Categoria" :value="c.Cod_Categoria">{{ c.Nombre_Categoria }}</option>
            </select>
            <InputError :message="form.errors.Cod_Categoria" />
          </div>

          <div class="mb-4">
            <label class="block">Imagen (opcional)</label>
            <input type="file" accept="image/*" @change="handleImage" />
            <InputError :message="form.errors.Imagen_Foro" />
            <div v-if="imagePreview" class="mt-2">
              <img :src="imagePreview" class="w-48 h-48 object-cover" />
            </div>
          </div>

          <div class="flex items-center space-x-3">
            <button @click.prevent="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
            <a :href="route('productos')" class="text-gray-600">Cancelar</a>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
