<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import CardPubli from '@/Components/CardPubli.vue';
import FunnelIcon from '@/Components/Icons/FunnelIcon.vue';

const props = defineProps({
    publicaciones: Array,
    currentUserId: Number,
    userEstado: String,
});




const selectedCategory = ref(null);
const isDropdownOpen = ref(false);

// Extraer categorías únicas de las publicaciones
const categories = computed(() => {
    if (!props.publicaciones || props.publicaciones.length === 0) return [];
    const unique = new Map();
    props.publicaciones.forEach(pub => {
        if (pub.categoria && pub.Cod_Categoria) {
            if (!unique.has(pub.Cod_Categoria)) {
                unique.set(pub.Cod_Categoria, pub.categoria.Nombre_Categoria);
            }
        }
    });
    return Array.from(unique, ([id, name]) => ({ Cod_Categoria: id, Nombre_Categoria: name }));
});

// Filtrar publicaciones según categoría seleccionada
const searchTerm = ref("");

const filteredPublicaciones = computed(() => {
    let pubs = props.publicaciones;
    if (selectedCategory.value) {
        pubs = pubs.filter(pub => pub.Cod_Categoria === selectedCategory.value);
    }
    if (searchTerm.value.trim() !== "") {
        const term = searchTerm.value.trim().toLowerCase();
        pubs = pubs.filter(pub => pub.Titulo_Publicacion && pub.Titulo_Publicacion.toLowerCase().includes(term));
    }
    return pubs;
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

                <div class="flex items-center space-x-4">
                    <!-- Botón Filtro de Categorías -->
                    <div class="relative">
                        <button
                            @click="isDropdownOpen = !isDropdownOpen"
                            class="flex items-center px-4 py-2 space-x-2 text-white transition bg-gray-600 rounded hover:bg-gray-700"
                        >
                            <FunnelIcon class="w-5 h-5" />
                            <span>Categories</span>
                        </button>

                        <!-- Desplegable -->
                        <div
                            v-if="isDropdownOpen"
                            class="absolute right-0 z-50 w-48 mt-2 bg-white border border-gray-300 rounded-lg shadow-lg"
                        >
                            <div class="p-2">
                                <button
                                    @click="selectedCategory = null; isDropdownOpen = false"
                                    class="block w-full px-4 py-2 text-sm text-left rounded hover:bg-gray-100"
                                    :class="{ 'bg-blue-100 font-semibold': selectedCategory === null }"
                                >
                                    Todas las categorías
                                </button>
                                <button
                                    v-for="cat in categories"
                                    :key="cat.Cod_Categoria"
                                    @click="selectedCategory = cat.Cod_Categoria; isDropdownOpen = false"
                                    class="block w-full px-4 py-2 text-sm text-left rounded hover:bg-gray-100"
                                    :class="{ 'bg-blue-100 font-semibold': selectedCategory === cat.Cod_Categoria }"
                                >
                                    {{ cat.Nombre_Categoria }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <Link :href="route('dashboard.create')" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                        Crear Publicación
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- Panel for inactive user -->
                <div v-if="userEstado === 'Inactivo'" class="p-4 mb-6 font-semibold text-red-700 bg-red-100 border border-red-400 rounded">
                    Usted ha sido inhabilitado por administración. Por favor contacte con soporte a través de WhatsApp: <a href="https://wa.me/73527947" target="_blank" class="underline">73527947</a>
                </div>

                <!-- Barra de búsqueda de publicaciones -->
                <div class="flex justify-end pb-2 mt-0">
                    <div class="p-5 bg-gray-200 rounded-lg">
                        <form @submit.prevent="() => {}" class="flex">
                            <div class="flex items-center justify-center w-10 p-5 bg-white border-r border-gray-200 rounded-tl-lg rounded-bl-lg">
                                <svg viewBox="0 0 20 20" aria-hidden="true" class="absolute w-5 transition pointer-events-none fill-gray-500">
                                    <path d="M16.72 17.78a.75.75 0 1 0 1.06-1.06l-1.06 1.06ZM9 14.5A5.5 5.5 0 0 1 3.5 9H2a7 7 0 0 0 7 7v-1.5ZM3.5 9A5.5 5.5 0 0 1 9 3.5V2a7 7 0 0 0-7 7h1.5ZM9 3.5A5.5 5.5 0 0 1 14.5 9H16a7 7 0 0 0-7-7v1.5Zm3.89 10.45 3.83 3.83 1.06-1.06-3.83-3.83-1.06 1.06ZM14.5 9a5.48 5.48 0 0 1-1.61 3.89l1.06 1.06A6.98 6.98 0 0 0 16 9h-1.5Zm-1.61 3.89A5.48 5.48 0 0 1 9 14.5V16a6.98 6.98 0 0 0 4.95-2.05l-1.06-1.06Z"></path>
                                </svg>
                            </div>
                            <input
                                type="text"
                                class="w-full max-w-[160px] bg-white pl-2 text-base font-semibold outline-0"
                                placeholder="Buscar por título..."
                                v-model="searchTerm"
                                @keyup.enter.prevent
                            >
                            <input
                                type="button"
                                value="Buscar"
                                class="p-2 font-semibold text-white transition-colors bg-blue-500 rounded-tr-lg rounded-br-lg hover:bg-blue-800"
                                @click="searchTerm = searchTerm"
                            >
                        </form>
                    </div>
                </div>

                <!-- Mostrar categoría seleccionada (opcional) -->
                <div v-if="selectedCategory" class="p-4 mb-4 border border-blue-200 rounded bg-blue-50">
                    <p class="text-sm text-gray-700">
                        Filtrando por: <strong>{{ categories.find(c => c.Cod_Categoria === selectedCategory)?.Nombre_Categoria }}</strong>
                        <button
                            @click="selectedCategory = null"
                            class="ml-2 text-blue-600 underline hover:text-blue-800"
                        >
                            (limpiar filtro)
                        </button>
                    </p>
                </div>

                <div v-if="filteredPublicaciones && filteredPublicaciones.length > 0" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="pub in filteredPublicaciones" :key="pub.id" class="flex justify-center">
                        <CardPubli
                            :title="pub.Titulo_Publicacion"
                            :subtitle="`Bs ${pub.Precio_Publicacion}`"
                            :image="pub.Imagen_Publicacion ? `/files/publicaciones/${pub.Imagen_Publicacion.split('/').pop()}` : null"
                            :description="pub.Descripcion_Publicacion"
                            :category="pub.categoria ? pub.categoria.Nombre_Categoria : pub.Cod_Categoria"
                            :id="pub.id"
                            :user="pub.vendedor ? pub.vendedor.user : null"
                            :currentUserId="$page.props.auth.user.id"
                            :isOwner="pub.vendedor && pub.vendedor.user_id === $page.props.auth.user.id"
                            :estado="pub.estado"
                            :publicacion="pub"
                            @edit="handleEdit"
                            @contact="handleContact"
                        />
                    </div>
                </div>
                <div v-else-if="selectedCategory" class="p-6 text-center text-gray-500 bg-white rounded-lg shadow-md">
                    <p>No hay publicaciones en esta categoría.</p>
                </div>
                <div v-else class="p-6 text-center text-gray-500 bg-white rounded-lg shadow-md">
                    <p>No hay publicaciones con ese nombre</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
