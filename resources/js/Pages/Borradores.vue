<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import CardPubli from '@/Components/CardPubli.vue';

defineProps({
    borradores: Array,
    currentUserId: Number,
});

function handleEdit(id) {
    // Redirige siempre usando la ruta absoluta
    router.visit(`/publicaciones/${id}/edit`);
}

function handleContact(publicationId) {
    alert('No puedes contactar con borradores. Esta es solo una vista privada de tus borradores.');
}
</script>

<template>
    <AppLayout title="Mis Borradores">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    MIS BORRADORES
                </h2>
                <Link :href="route('dashboard.create')" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                    Crear Publicación
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="borradores && borradores.length > 0" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="pub in borradores" :key="pub.id" class="flex justify-center">
                        <CardPubli
                            :title="pub.Titulo_Publicacion"
                            :subtitle="`Bs ${pub.Precio_Publicacion}`"
                            :description="pub.Descripcion_Publicacion"
                            :category="pub.categoria ? pub.categoria.Nombre_Categoria : pub.Cod_Categoria"
                            :id="pub.id"
                            :user="pub.vendedor ? pub.vendedor.user : null"
                            :currentUserId="currentUserId"
                            :isOwner="true"
                            :estado="pub.estado"
                            :publicacion="pub"
                            @edit="handleEdit"
                            @contact="handleContact"
                        />
                    </div>
                </div>
                <div v-else class="p-6 text-center text-gray-500 bg-white rounded-lg shadow-md">
                    <p>No tienes borradores aún. ¡Crea una publicación!</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
