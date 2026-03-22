<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import CardPubli from '@/Components/CardPubli.vue';
import { Filter, Search, Plus, ArchiveX } from 'lucide-vue-next';

const props = defineProps({
    publicaciones: Array,
    mejores: { type: Array, default: () => [] },
    currentUserId: Number,
    userEstado: String,
});

const page = usePage();

const selectedCategory = ref(null);
const isDropdownOpen = ref(false);

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

const searchTerm = ref("");

const filteredPublicaciones = computed(() => {
    let pubs = props.publicaciones || [];
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
    try {
        if (typeof route === 'function') {
            router.visit(route('publicaciones.edit', id));
        } else {
            router.visit(`/publicaciones/${id}/edit`);
        }
    } catch (e) {
        console.error('Editar publicación:', e);
    }
}

function handleContact(publicationId) {
    const publication = props.publicaciones.find(pub => pub.id === publicationId);

    if (!publication || !publication.vendedor) {
        alert('No se pudo encontrar al vendedor.');
        return;
    }

    try {
        const ownerId = publication.vendedor?.user?.id ?? publication.vendedor?.user_id ?? null;
        const meId = page.props?.auth?.user?.id ?? null;
        if (ownerId && meId && Number(ownerId) === Number(meId)) {
            alert('No puedes contactar a tu propia publicación.');
            return;
        }
    } catch (e) {}

    let sellerPhone = publication.vendedor.Telefono || publication.vendedor.telefono || null;
    if (!sellerPhone && publication.vendedor.user) {
        sellerPhone = publication.vendedor.user.Telefono || publication.vendedor.user.telefono || (publication.vendedor.user.usuarioCampusMarket && publication.vendedor.user.usuarioCampusMarket.Telefono) || null;
    }

    if (!sellerPhone) {
        alert('El vendedor no tiene número de teléfono disponible.');
        return;
    }

    const buyer = page.props?.auth?.user || null;
    const buyerName = buyer?.name || '';
    const buyerPhone = buyer?.Telefono || buyer?.telefono || buyer?.usuarioCampusMarket?.Telefono || '';

    let normalized = String(sellerPhone).replace(/\D+/g, '');
    if (!normalized) {
        alert('El número de teléfono del vendedor no es válido.');
        return;
    }

    const title = publication.Titulo_Publicacion || publication.title || '';
    const message = `Hola, soy ${buyerName}. Estoy interesado en tu publicación "${title}".` + (buyerPhone ? ` Mi teléfono es ${buyerPhone}.` : '');
    const encoded = encodeURIComponent(message);

    const waUrl = `https://wa.me/${normalized}?text=${encoded}`;
    window.open(waUrl, '_blank');
}
</script>

<template>
    <AppLayout title="Inicio">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                    Mercado Universitario
                </h2>

                <div class="flex items-center gap-3">
                    <Link 
                        :href="route('dashboard.create')" 
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition-all bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-500 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 active:scale-95"
                    >
                        <Plus class="w-4 h-4" />
                        Crear Publicación
                    </Link>
                </div>
            </div>
        </template>

        <!-- Panel de Inhabilitado -->
        <div v-if="userEstado === 'Inactivo'" class="max-w-7xl mx-auto mt-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center p-4 border-l-4 border-red-500 bg-red-50 rounded-r-lg">
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-red-800">Cuenta Inhabilitada</h3>
                    <div class="mt-1 text-sm text-red-700">
                        Usted ha sido inhabilitado por administración. Por favor contacte con soporte a través de WhatsApp: 
                        <a href="https://wa.me/73527947" target="_blank" class="font-semibold underline hover:text-red-900">73527947</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Barra de Herramientas Premium -->
                <div class="flex flex-col gap-4 p-4 mb-8 bg-white border border-gray-100 shadow-sm sm:flex-row sm:items-center sm:justify-between rounded-xl">
                    <!-- Búsqueda -->
                    <div class="relative flex-1 max-w-lg">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <Search class="w-5 h-5 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            v-model="searchTerm"
                            class="block w-full py-2.5 pl-10 pr-3 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                            placeholder="Buscar artículos, libros..."
                        >
                    </div>

                    <!-- Filtros -->
                    <div class="relative z-20 flex items-center gap-2">
                        <div class="relative">
                            <button
                                @click="isDropdownOpen = !isDropdownOpen"
                                :class="[
                                    'flex items-center gap-2 px-4 py-2.5 text-sm font-medium transition-all border rounded-lg',
                                    selectedCategory ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'
                                ]"
                            >
                                <Filter class="w-4 h-4" />
                                <span>{{ selectedCategory ? categories.find(c => c.Cod_Categoria === selectedCategory)?.Nombre_Categoria : 'Categorías' }}</span>
                            </button>

                            <!-- Dropdown -->
                            <div
                                v-show="isDropdownOpen"
                                @click.away="isDropdownOpen = false"
                                class="absolute right-0 w-56 mt-2 origin-top-right bg-white border border-gray-100 rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none"
                            >
                                <div class="p-1">
                                    <button
                                        @click="selectedCategory = null; isDropdownOpen = false"
                                        :class="[
                                            'flex w-full items-center px-4 py-2 text-sm rounded-lg transition-colors',
                                            selectedCategory === null ? 'bg-indigo-50 font-semibold text-indigo-700' : 'text-gray-700 hover:bg-gray-50'
                                        ]"
                                    >
                                        Todas las categorías
                                    </button>
                                    <button
                                        v-for="cat in categories"
                                        :key="cat.Cod_Categoria"
                                        @click="selectedCategory = cat.Cod_Categoria; isDropdownOpen = false"
                                        :class="[
                                            'flex w-full items-center px-4 py-2 text-sm rounded-lg transition-colors mt-1',
                                            selectedCategory === cat.Cod_Categoria ? 'bg-indigo-50 font-semibold text-indigo-700' : 'text-gray-700 hover:bg-gray-50'
                                        ]"
                                    >
                                        {{ cat.Nombre_Categoria }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button 
                            v-if="selectedCategory || searchTerm"
                            @click="selectedCategory = null; searchTerm = ''"
                            class="px-3 py-2.5 text-sm font-medium text-gray-500 transition-colors hover:text-gray-900"
                        >
                            Limpiar
                        </button>
                    </div>
                </div>

                <!-- Sección Mejores Valorados (Sólo si no hay búsqueda/filtro) -->
                <div v-if="!selectedCategory && !searchTerm && props.mejores && props.mejores.length > 0" class="mb-12">
                    <div class="flex items-center gap-3 mb-6">
                        <h3 class="text-xl font-bold tracking-tight text-gray-900">Destacados</h3>
                        <div class="h-px bg-gray-200 flex-1"></div>
                    </div>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div v-for="pub in props.mejores" :key="pub.id" class="flex justify-center transition-transform hover:-translate-y-1">
                            <CardPubli
                                :title="pub.Titulo_Publicacion"
                                :subtitle="`Bs ${pub.Precio_Publicacion}`"
                                :description="pub.Descripcion_Publicacion"
                                :category="pub.categoria ? pub.categoria.Nombre_Categoria : pub.Cod_Categoria"
                                :id="pub.id"
                                :user="pub.vendedor?.user || null"
                                :currentUserId="page.props.auth.user.id"
                                :isOwner="pub.vendedor?.user_id === page.props.auth.user.id"
                                :estado="pub.estado"
                                :publicacion="pub"
                                @edit="handleEdit"
                                @contact="handleContact"
                            />
                        </div>
                    </div>
                </div>

                <!-- Lista de Publicaciones Normales -->
                <div class="flex items-center gap-3 mb-6">
                    <h3 class="text-xl font-bold tracking-tight text-gray-900">
                        {{ searchTerm ? 'Resultados de Búsqueda' : (selectedCategory ? 'Resultados de Categoría' : 'Recientes') }}
                    </h3>
                    <div class="h-px bg-gray-200 flex-1"></div>
                </div>

                <div v-if="filteredPublicaciones && filteredPublicaciones.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div v-for="pub in filteredPublicaciones" :key="pub.id" class="flex justify-center transition-transform hover:-translate-y-1">
                        <CardPubli
                            :title="pub.Titulo_Publicacion"
                            :subtitle="`Bs ${pub.Precio_Publicacion}`"
                            :description="pub.Descripcion_Publicacion"
                            :category="pub.categoria ? pub.categoria.Nombre_Categoria : pub.Cod_Categoria"
                            :id="pub.id"
                            :user="pub.vendedor?.user || null"
                            :currentUserId="page.props.auth.user.id"
                            :isOwner="pub.vendedor?.user_id === page.props.auth.user.id"
                            :estado="pub.estado"
                            :publicacion="pub"
                            @edit="handleEdit"
                            @contact="handleContact"
                        />
                    </div>
                </div>

                <!-- Empty States (Estado Vacío) -->
                <div v-else class="flex flex-col items-center justify-center p-12 text-center bg-white border border-gray-100 rounded-2xl shadow-sm">
                    <div class="flex items-center justify-center w-16 h-16 mb-4 bg-gray-50 rounded-full">
                        <ArchiveX class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="mb-1 text-lg font-bold text-gray-900">No se encontraron resultados</h3>
                    <p class="mb-6 text-sm text-gray-500 max-w-sm">
                        {{ selectedCategory || searchTerm ? 'Intenta ajustando tus filtros de búsqueda o eliminando palabras.' : 'Aún no hay publicaciones en el sistema. ¡Sé el primero en vender algo!' }}
                    </p>
                    <button 
                        v-if="selectedCategory || searchTerm"
                        @click="selectedCategory = null; searchTerm = ''" 
                        class="px-4 py-2 text-sm font-semibold text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors"
                    >
                        Limpiar Filtros
                    </button>
                    <Link 
                        v-else
                        :href="route('dashboard.create')" 
                        class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-500 transition-colors shadow-sm"
                    >
                        Crear Publicación
                    </Link>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
