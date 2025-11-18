<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FunnelIcon from '@/Components/Icons/FunnelIcon.vue';

const page = usePage();
const items = computed(() => (page.props.foros ?? page.props.productos) ?? []);

const selectedCategory = ref(null);
const isDropdownOpen = ref(false);

const categories = computed(() => {
    if (!items.value || items.value.length === 0) return [];
    const unique = new Map();
    items.value.forEach(i => {
        if (i.categoria && i.Cod_Categoria) {
            if (!unique.has(i.Cod_Categoria)) {
                unique.set(i.Cod_Categoria, i.categoria.Nombre_Categoria);
            }
        }
    });
    return Array.from(unique, ([id, name]) => ({ Cod_Categoria: id, Nombre_Categoria: name }));
});

const searchTerm = ref("");

const filteredItems = computed(() => {
    let arr = items.value;
    if (selectedCategory.value) {
        arr = arr.filter(i => i.Cod_Categoria === selectedCategory.value);
    }
    if (searchTerm.value.trim() !== "") {
        const term = searchTerm.value.trim().toLowerCase();
        arr = arr.filter(i => {
            const titulo = i.Titulo_Foro || i.Titulo_Publicacion || i.Titulo || i.nombre || '';
            return titulo.toLowerCase().includes(term);
        });
    }
    return arr;
});
</script>

<template>
    <AppLayout title="Foros">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Foros
                </h2>

                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button @click="isDropdownOpen = !isDropdownOpen" class="flex items-center px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">
                            <FunnelIcon class="w-5 h-5 mr-2" />
                            <span class="text-sm">Categories</span>
                        </button>
                        <div v-if="isDropdownOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-300 z-50">
                            <div class="p-2">
                                <button @click="selectedCategory = null; isDropdownOpen = false" class="block w-full text-left px-4 py-2 text-sm rounded hover:bg-gray-100">Todas las categorías</button>
                                <button v-for="cat in categories" :key="cat.Cod_Categoria" @click="selectedCategory = cat.Cod_Categoria; isDropdownOpen = false" class="block w-full text-left px-4 py-2 text-sm rounded hover:bg-gray-100">{{ cat.Nombre_Categoria }}</button>
                            </div>
                        </div>
                    </div>

                    <Link :href="route('productos.create')" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                        Nuevo foro
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Barra de búsqueda de foros -->
                <div class="flex justify-end p-5">
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

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="selectedCategory" class="mb-4 p-3 bg-blue-50 rounded border border-blue-200">
                            Filtrando por: <strong>{{ categories.find(c => c.Cod_Categoria === selectedCategory)?.Nombre_Categoria }}</strong>
                            <button @click="selectedCategory = null" class="ml-2 text-blue-600 underline">(limpiar)</button>
                        </div>

                        <div v-if="filteredItems && filteredItems.length > 0">
                            <ul class="space-y-2">
                                <li v-for="it in filteredItems" :key="it.ID_Foro || it.id" class="p-3 bg-white rounded shadow-sm flex items-center space-x-4">
                                    <div class="card flex-shrink-0">
                                        <template v-if="it.Imagen_Foro">
                                            <img :src="route('files.foros', it.Imagen_Foro.split('/').pop())" alt="imagen foro" style="width:100%;height:100%;object-fit:cover;border-radius:6px;" />
                                        </template>
                                        <div v-else class="no-image">No imagen</div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <strong>{{ it.Titulo_Foro || it.Titulo_Publicacion || it.Titulo || it.nombre || 'Sin título' }}</strong>
                                                <div class="text-sm text-gray-600">Categoria: {{ it.categoria ? it.categoria.Nombre_Categoria : it.Cod_Categoria }}</div>
                                            </div>
                                            <div>
                                                <Link :href="route('productos.show', it.ID_Foro || it.id)" class="px-3 py-2 bg-yellow-300 text-sm rounded">Ver foro</Link>
                                            </div>
                                        </div>

                                        <div class="mt-2 text-sm text-gray-700">{{ it.Descripcion_Foro || it.Descripcion || it.descripcion || '' }}</div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div v-else class="text-gray-500">No hay foros para esta selección.</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.uiverse-btn {
    font-family: Arial, Helvetica, sans-serif;
    font-weight: bold;
    color: white;
    background-color: #171717;
    padding: 1em 2em;
    border: none;
    border-radius: .6rem;
    position: relative;
    cursor: pointer;
    overflow: hidden;
}

.uiverse-btn span:not(:nth-child(6)) {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    height: 30px;
    width: 30px;
    background-color: #0c66ed;
    border-radius: 50%;
    transition: .6s ease;
}

.uiverse-btn span:nth-child(6) {
    position: relative;
}

.uiverse-btn span:nth-child(1) {
    transform: translate(-3.3em, -4em);
}

.uiverse-btn span:nth-child(2) {
    transform: translate(-6em, 1.3em);
}

.uiverse-btn span:nth-child(3) {
    transform: translate(-.2em, 1.8em);
}

.uiverse-btn span:nth-child(4) {
    transform: translate(3.5em, 1.4em);
}

.uiverse-btn span:nth-child(5) {
    transform: translate(3.5em, -3.8em);
}

.uiverse-btn:hover span:not(:nth-child(6)) {
    transform: translate(-50%, -50%) scale(4);
    transition: 1.5s ease;
}

/* Card styles for foro image */
.card {
    width: 190px;
    height: 254px;
    background: transparent;
    border: 2px solid #0813aff3;
    box-shadow: 2px 2px 15px #000000 inset;
    text-align: center;
    color: #5c0909;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Pacifico', sans-serif;
    border-radius: 6px;
    overflow: hidden;
}

.card:hover {
    color: #07ff07;
    box-shadow: 2px 2px 15px #07ff07 inset;
}

.no-image {
    font-size: 12px;
    color: #9ca3af;
}
</style>
