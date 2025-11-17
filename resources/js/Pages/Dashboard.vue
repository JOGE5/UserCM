<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import CardPubli from '@/Components/CardPubli.vue';
import FunnelIcon from '@/Components/Icons/FunnelIcon.vue';

const props = defineProps({
    publicaciones: Array,
    currentUserId: Number,
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
const filteredPublicaciones = computed(() => {
    if (!selectedCategory.value) {
        return props.publicaciones;
    }
    return props.publicaciones.filter(pub => pub.Cod_Categoria === selectedCategory.value);
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
                            class="flex items-center space-x-2 px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition"
                        >
                            <FunnelIcon class="w-5 h-5" />
                            <span>Categories</span>
                        </button>

                        <!-- Desplegable -->
                        <div
                            v-if="isDropdownOpen"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-300 z-50"
                        >
                            <div class="p-2">
                                <button
                                    @click="selectedCategory = null; isDropdownOpen = false"
                                    class="block w-full text-left px-4 py-2 text-sm rounded hover:bg-gray-100"
                                    :class="{ 'bg-blue-100 font-semibold': selectedCategory === null }"
                                >
                                    Todas las categorías
                                </button>
                                <button
                                    v-for="cat in categories"
                                    :key="cat.Cod_Categoria"
                                    @click="selectedCategory = cat.Cod_Categoria; isDropdownOpen = false"
                                    class="block w-full text-left px-4 py-2 text-sm rounded hover:bg-gray-100"
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
                <!-- Mostrar categoría seleccionada (opcional) -->
                <div v-if="selectedCategory" class="mb-4 p-4 bg-blue-50 rounded border border-blue-200">
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
                    <p>No hay publicaciones aún. ¡Crea una!</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
