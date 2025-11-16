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
    Imagen_Publicacion: null,
    Cod_Categoria: '',
});

const submit = () => {
    form.post(route('publicaciones.store'));
};

const handleImageChange = (event) => {
    form.Imagen_Publicacion = event.target.files[0];
};
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
                                <input
                                    id="imagen"
                                    type="file"
                                    accept="image/*"
                                    @change="handleImageChange"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
                                />
                                <p v-if="form.errors.Imagen_Publicacion" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.Imagen_Publicacion }}
                                </p>
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
