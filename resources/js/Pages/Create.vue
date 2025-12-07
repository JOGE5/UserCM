<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    categorias: Array,
});

const form = useForm({
    Titulo_Publicacion: '',
    Descripcion_Publicacion: '',
    Estado_Publicacion: true,
    Precio_Publicacion: '',
    Imagen_Publicacion: [],
    Cod_Categoria: '',
});

const imagePreview = ref([]);
const fileInput = ref(null);

const submit = () => {
    form.post(route('publicaciones.store'));
};

// Procesa un array de File (desde input o drop)
function processFiles(files) {
    const arr = Array.from(files || []);
    if (arr.length === 0) return;
    // Combinar con los ya seleccionados (asegurando que solo mantengamos File objects)
    const existing = Array.isArray(form.Imagen_Publicacion) ? form.Imagen_Publicacion.filter(f => f instanceof File) : [];
    // Deduplicar por nombre+size para evitar añadir el mismo archivo varias veces
    const combined = existing.concat(arr).filter((f, idx, self) => {
        return f instanceof File && self.findIndex(g => g.name === f.name && g.size === f.size) === idx;
    });
    const allowed = combined.slice(0, 3);
    // Validar tamaño máximo 2MB por archivo
    const tooLarge = allowed.find(f => f.size > 2 * 1024 * 1024);
    if (tooLarge) {
        alert('Una de las imágenes supera 2MB. Elige imágenes más pequeñas.');
        imagePreview.value = [];
        form.Imagen_Publicacion = [];
        return;
    }

    form.Imagen_Publicacion = allowed;
    // Recrear previews según las imagenes permitidas
    imagePreview.value = [];
    allowed.forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
}

const handleImageChange = (event) => {
    processFiles(event.target.files);
    // limpiar valor para permitir seleccionar los mismos archivos de nuevo o añadir más
    try {
        if (fileInput.value && fileInput.value instanceof HTMLInputElement) {
            fileInput.value.value = null;
        } else {
            event.target.value = '';
        }
    } catch (e) {}
};

const handleDrop = (e) => {
    e.preventDefault();
    const dt = e.dataTransfer;
    let files = [];
    if (dt && dt.items && dt.items.length > 0) {
        for (let i = 0; i < dt.items.length; i++) {
            const item = dt.items[i];
            if (item.kind === 'file') {
                const f = item.getAsFile();
                if (f) files.push(f);
            }
        }
    } else if (dt && dt.files && dt.files.length > 0) {
        files = Array.from(dt.files);
    }

    if (files.length > 0) processFiles(files);
};

// Eliminar imagen seleccionada antes de enviar
function removeSelected(index) {
    // remover preview
    if (imagePreview.value && imagePreview.value.length > index) {
        imagePreview.value.splice(index, 1);
    }
    // remover archivo correspondiente
    if (form.Imagen_Publicacion && form.Imagen_Publicacion.length > index) {
        form.Imagen_Publicacion.splice(index, 1);
    }
}
</script>

<template>
    <AppLayout title="Crear Publicación">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Publicación
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
                                    Imagen
                                </label>
                                <div
                                    @dragover.prevent
                                    @drop.prevent="handleDrop"
                                    class="relative mt-1 block w-full rounded-md border-dashed border-2 border-gray-300 p-4 text-center bg-gray-50"
                                >
                                    <input
                                        id="imagen"
                                        ref="fileInput"
                                        type="file"
                                        accept="image/*"
                                        multiple
                                        @change="handleImageChange"
                                        class="w-full opacity-0 absolute inset-0 h-full cursor-pointer"
                                    />
                                    <div class="relative">
                                        <div class="pointer-events-none">
                                            <p class="text-sm text-gray-600">Arrastra y suelta hasta 3 imágenes aquí, o haz clic para seleccionar</p>
                                            <p class="text-xs text-gray-400">(Máx. 3 imágenes, 2MB cada una)</p>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="form.errors.Imagen_Publicacion" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.Imagen_Publicacion }}
                                </p>
                                <!-- Vistas previas de imagenes -->
                                <div v-if="imagePreview && imagePreview.length > 0" class="mt-4 flex gap-4">
                                    <div v-for="(src, idx) in imagePreview" :key="idx" class="relative drop-shadow-xl w-36 h-48 overflow-hidden rounded-xl bg-[#3d3c3d]">
                                        <button @click.stop="removeSelected(idx)" class="absolute top-1 right-1 z-10 bg-white text-red-600 rounded-full w-6 h-6 flex items-center justify-center text-xs">×</button>
                                        <img :src="src" class="absolute inset-0 w-full h-full object-cover" />
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
                                    {{ form.processing ? 'Guardando...' : 'Crear Publicación' }}
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
