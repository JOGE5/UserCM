<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import CardPubli from '@/Components/CardPubli.vue';

defineProps({
    publicaciones: Array,
    currentUserId: Number,
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

function handleContact(publicationId) {
    console.log('handleContact called with publicationId:', publicationId);
    console.log('publicaciones:', publicaciones);
    console.log('currentUserId:', currentUserId);
    console.log('currentUserId from page:', $page.props.auth.user.id);

    // Obtener el user_id del vendedor desde las publicaciones
    const publication = publicaciones.find(pub => pub.id === publicationId);
    console.log('publication found:', publication);

    if (!publication || !publication.vendedor || !publication.vendedor.user) {
        alert('No se pudo encontrar al vendedor.');
        return;
    }

    const sellerUserId = publication.vendedor.user_id;
    console.log('sellerUserId:', sellerUserId);
    console.log('publication.vendedor:', publication.vendedor);
    console.log('publication.vendedor.user:', publication.vendedor.user);

    // Crear chat privado con el vendedor
    fetch('/chats/private', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ seller_id: sellerUserId })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.chat_id) {
            router.visit('/chats/' + data.chat_id);
        } else {
            alert('Error al crear el chat: ' + (data.error || 'Respuesta inválida'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al contactar al vendedor: ' + error.message);
    });
}
</script>

<template>
    <AppLayout title="Inicio">
        <template #header>
            <div class="flex items-center justify-between">

            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                PUBLICACIONES
            </h2>

            <Link :href="route('dashboard.create')" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                Crear Publicación
            </Link>

            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="publicaciones && publicaciones.length > 0" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="pub in publicaciones" :key="pub.id" class="flex justify-center">
                        <CardPubli
                            :title="pub.Titulo_Publicacion"
                            :subtitle="`Bs ${pub.Precio_Publicacion}`"
                            :image="pub.Imagen_Publicacion ? `/files/publicaciones/${pub.Imagen_Publicacion.split('/').pop()}` : null"
                            :description="pub.Descripcion_Publicacion"
                            :category="pub.categoria ? pub.categoria.Nombre_Categoria : pub.Cod_Categoria"
                            :id="pub.id"
                            :user="pub.vendedor ? pub.vendedor.user : null"
                            :currentUserId="$page.props.auth.user.id"
                            @edit="handleEdit"
                            @contact="handleContact"
                        />
                    </div>
                </div>
                <div v-else class="p-6 text-center text-gray-500 bg-white rounded-lg shadow-md">
                    <p>No hay publicaciones aún. ¡Crea una!</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
