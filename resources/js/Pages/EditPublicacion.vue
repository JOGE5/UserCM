<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    publicacion: Object,
    categorias: Array,
});

const form = useForm({
    Titulo_Publicacion: props.publicacion.Titulo_Publicacion || '',
    Descripcion_Publicacion: props.publicacion.Descripcion_Publicacion || '',
    Estado_Publicacion: props.publicacion.Estado_Publicacion ? true : false,
    Precio_Publicacion: props.publicacion.Precio_Publicacion || '',
    Imagen_Publicacion: null,
    Cod_Categoria: props.publicacion.Cod_Categoria || '',
});

const imagePreview = ref(null);

const submit = () => {
    // Si no hay imagen seleccionada, no incluirla en el request
    // Inertia.js solo debe enviar campos que realmente cambiaron
    if (!form.Imagen_Publicacion) {
        delete form.Imagen_Publicacion;
    }
    form.put(route('publicaciones.update', props.publicacion.id));
};

const handleDelete = () => {
    if (confirm('¿Estás seguro de que deseas eliminar esta publicación? Esta acción no se puede deshacer.')) {
        router.delete(route('publicaciones.destroy', props.publicacion.id));
    }
};

const handleDraft = () => {
    if (confirm('¿Deseas convertir esta publicación a borrador? Solo tú podrás verla.')) {
        router.patch(route('publicaciones.draft', props.publicacion.id));
    }
};

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validar que sea imagen
        if (!file.type.startsWith('image/')) {
            alert('Por favor selecciona un archivo de imagen válido (JPG, PNG, GIF, etc)');
            event.target.value = '';
            imagePreview.value = null;
            return;
        }
        // Validar tamaño máximo 2MB
        if (file.size > 2 * 1024 * 1024) {
            alert('La imagen no puede superar 2MB');
            event.target.value = '';
            imagePreview.value = null;
            return;
        }
        form.Imagen_Publicacion = file;
        // Crear vista previa
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};
</script>

<template>
    <AppLayout title="Editar Publicación">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Publicación
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Título -->
                            <div>
                                <label for="titulo" class="block text-sm font-medium text-gray-700">
                                    Título de la Publicación
                                </label>
                                <input
                                    id="titulo"
                                    v-model="form.Titulo_Publicacion"
                                    type="text"
                                    maxlength="200"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
                                    placeholder="Ingresa el título"
                                    required
                                />
                                <p v-if="form.errors.Titulo_Publicacion" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.Titulo_Publicacion }}
                                </p>
                            </div>

                            <!-- Descripción -->
                            <div>
                                <label for="descripcion" class="block text-sm font-medium text-gray-700">
                                    Descripción
                                </label>
                                <textarea
                                    id="descripcion"
                                    v-model="form.Descripcion_Publicacion"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
                                    placeholder="Describe tu publicación"
                                    required
                                ></textarea>
                                <p v-if="form.errors.Descripcion_Publicacion" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.Descripcion_Publicacion }}
                                </p>
                            </div>

                            <!-- Precio -->
                            <div>
                                <label for="precio" class="block text-sm font-medium text-gray-700">
                                    Precio
                                </label>
                                <input
                                    id="precio"
                                    v-model="form.Precio_Publicacion"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
                                    placeholder="0.00"
                                    required
                                />
                                <p v-if="form.errors.Precio_Publicacion" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.Precio_Publicacion }}
                                </p>
                            </div>

                            <!-- Categoría -->
                            <div>
                                <label for="categoria" class="block text-sm font-medium text-gray-700">
                                    Categoría
                                </label>
                                <select
                                    id="categoria"
                                    v-model="form.Cod_Categoria"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
                                    required
                                >
                                    <option value="">Selecciona una categoría</option>
                                    <option
                                        v-for="categoria in categorias"
                                        :key="categoria.Cod_Categoria"
                                        :value="categoria.Cod_Categoria"
                                    >
                                        {{ categoria.Nombre_Categoria }}
                                    </option>
                                </select>
                                <p v-if="form.errors.Cod_Categoria" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.Cod_Categoria }}
                                </p>
                            </div>

                            <!-- Imagen -->
                            <div>
                                <label for="imagen" class="block text-sm font-medium text-gray-700">
                                    Imagen (sube solo si deseas reemplazar la actual)
                                </label>
                                <input
                                    id="imagen"
                                    type="file"
                                    accept="image/*"
                                    @change="handleImageChange"
                                    :required="!props.publicacion.Imagen_Publicacion"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
                                />
                                <p v-if="form.errors.Imagen_Publicacion" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.Imagen_Publicacion }}
                                </p>
                                <!-- Vista previa de nueva imagen (si se sube) -->
                                <div v-if="imagePreview" class="mt-4">
                                    <p class="text-sm text-gray-600 mb-2">Vista previa (nueva imagen):</p>
                                    <div class="flex justify-center">
                                        <div class="relative drop-shadow-xl w-48 h-64 overflow-hidden rounded-xl bg-[#3d3c3d]">
                                            <img
                                                :src="imagePreview"
                                                alt="Vista previa de nueva imagen"
                                                class="absolute inset-0 w-full h-full object-cover"
                                            />
                                            <div class="absolute w-56 h-48 bg-white blur-[50px] -left-1/2 -top-1/2"></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Imagen actual (si existe y no hay vista previa de nueva) -->
                                <div v-else-if="props.publicacion.Imagen_Publicacion" class="mt-4">
                                    <p class="text-sm text-gray-600 mb-2">Imagen actual:</p>
                                    <div class="flex justify-center">
                                        <div class="relative drop-shadow-xl w-48 h-64 overflow-hidden rounded-xl bg-[#3d3c3d]">
                                            <img
                                                :src="`/files/publicaciones/${props.publicacion.Imagen_Publicacion.split('/').pop()}`"
                                                alt="Imagen actual"
                                                class="absolute inset-0 w-full h-full object-cover"
                                            />
                                            <div class="absolute w-56 h-48 bg-white blur-[50px] -left-1/2 -top-1/2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="flex items-center">
                                <input
                                    id="estado"
                                    v-model="form.Estado_Publicacion"
                                    type="checkbox"
                                    class="rounded border-gray-300"
                                />
                                <label for="estado" class="ml-2 text-sm font-medium text-gray-700">
                                    Publicación activa
                                </label>
                            </div>

                            <!-- Botones -->
                            <div class="flex gap-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Guardando...' : 'Actualizar Publicación' }}
                                </button>
                                <button
                                    type="button"
                                    @click="handleDraft"
                                    class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700"
                                >
                                    Convertir a Borrador
                                </button>
                                <button
                                    type="button"
                                    @click="handleDelete"
                                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                                >
                                    Eliminar
                                </button>
                                <a
                                    :href="route('dashboard')"
                                    class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
                                >
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
