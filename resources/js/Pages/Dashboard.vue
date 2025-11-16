<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import CardPubli from '@/Components/CardPubli.vue';

defineProps({
    publicaciones: Array,
});

function handleEdit(id) {
    // Intenta navegar a la ruta de edición si existe; en caso contrario, muestra consola
    try {
        // si Ziggy `route` está disponible
        if (typeof route === 'function') {
            router.visit(route('publicaciones.edit', id));
        } else {
            router.visit(`/publicaciones/${id}/edit`);
        }
    } catch (e) {
        console.log('Editar publicación:', id, e);
        alert('Navegar a edición: /publicaciones/' + id + '/edit (si la ruta existe).');
    }
}

function handleContact(id) {
    // Acción por defecto: abrir la página de la publicación (si existe)
    try {
        if (typeof route === 'function') {
            router.visit(route('publicaciones.show', id));
        } else {
            router.visit(`/publicaciones/${id}`);
        }
    } catch (e) {
        console.log('Contactar por publicación:', id, e);
        alert('Abrir página de la publicación: /publicaciones/' + id + ' (si la ruta existe).');
    }
}
</script>

<template>
    <AppLayout title="Inicio">
        <template #header>
            <div class="flex justify-between items-center">
            
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                PUBLICACIONES 
            </h2>

            <Link :href="route('dashboard.create')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Crear Publicación
            </Link>

            </div> 
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="publicaciones && publicaciones.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="pub in publicaciones" :key="pub.id" class="flex justify-center">
                        <CardPubli
                            :title="pub.Titulo_Publicacion"
                            :subtitle="`$${pub.Precio_Publicacion}`"
                            :image="pub.Imagen_Publicacion ? `/files/publicaciones/${pub.Imagen_Publicacion.split('/').pop()}` : null"
                            :description="pub.Descripcion_Publicacion"
                            :category="pub.categoria ? pub.categoria.Nombre_Categoria : pub.Cod_Categoria"
                            :id="pub.id"
                            @edit="handleEdit"
                            @contact="handleContact"
                        />
                    </div>
                </div>
                <div v-else class="bg-white rounded-lg shadow-md p-6 text-center text-gray-500">
                    <p>No hay publicaciones aún. ¡Crea una!</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
