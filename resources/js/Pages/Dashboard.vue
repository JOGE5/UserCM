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
    // Buscar la publicación
    const publication = publicaciones.find(pub => pub.id === publicationId);
    console.log('publication found:', publication);

    if (!publication || !publication.vendedor) {
        alert('No se pudo encontrar al vendedor.');
        return;
    }

    // Intentar obtener el teléfono del vendedor en varias rutas posibles
    let sellerPhone = publication.vendedor.Telefono || publication.vendedor.telefono || null;
    if (!sellerPhone && publication.vendedor.user) {
        sellerPhone = publication.vendedor.user.Telefono || publication.vendedor.user.telefono || (publication.vendedor.user.usuarioCampusMarket && publication.vendedor.user.usuarioCampusMarket.Telefono) || null;
    }

    if (!sellerPhone) {
        alert('El vendedor no tiene número de teléfono disponible.');
        return;
    }

    // Número del comprador (usuario autenticado) si está disponible
    const buyer = $page.props && $page.props.auth && $page.props.auth.user ? $page.props.auth.user : null;
    const buyerName = buyer ? (buyer.name || '') : '';
    const buyerPhone = buyer ? (buyer.Telefono || buyer.telefono || (buyer.usuarioCampusMarket && buyer.usuarioCampusMarket.Telefono) || '') : '';

    // Normalizar número: quitar todo lo que no sea dígito
    let normalized = String(sellerPhone).replace(/\D+/g, '');
    if (!normalized) {
        alert('El número de teléfono del vendedor no es válido.');
        return;
    }

    // Construir mensaje prellenado
    const title = publication.Titulo_Publicacion || publication.title || '';
    const message = `Hola, soy ${buyerName}. Estoy interesado en tu publicación "${title}".` + (buyerPhone ? ` Mi teléfono es ${buyerPhone}.` : '');
    const encoded = encodeURIComponent(message);

    // Abrir chat de WhatsApp en nueva pestaña
    const waUrl = `https://wa.me/${normalized}?text=${encoded}`;
    window.open(waUrl, '_blank');
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
                <!-- Barra de búsqueda de publicaciones -->
                                <div class="flex justify-end pb-2 mt-0">
                                    <div class="rounded-lg bg-gray-200 p-5">
                                        <form @submit.prevent="() => {}" class="flex">
                                            <div class="flex w-10 items-center justify-center rounded-tl-lg rounded-bl-lg border-r border-gray-200 bg-white p-5">
                                                <svg viewBox="0 0 20 20" aria-hidden="true" class="pointer-events-none absolute w-5 fill-gray-500 transition">
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
                                                class="bg-blue-500 p-2 rounded-tr-lg rounded-br-lg text-white font-semibold hover:bg-blue-800 transition-colors"
                                                @click="searchTerm = searchTerm"
                                            >
                                        </form>
                                    </div>
                                </div>

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
