  <script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CardPubli from '@/Components/CardPubli.vue';

defineProps({
    publicaciones: Array,
    currentUserId: Number,
});
</script>

<template>
  <AppLayout title="Favoritos">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Mis Favoritos
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div v-if="publicaciones && publicaciones.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <CardPubli 
                  v-for="pub in publicaciones" 
                  :key="pub.ID_Publicacion || pub.id" 
                  :title="pub.Titulo_Publicacion"
                  :subtitle="`Bs ${pub.Precio_Publicacion}`"
                  :description="pub.Descripcion_Publicacion"
                  :category="pub.categoria ? pub.categoria.Nombre_Categoria : pub.Cod_Categoria"
                  :id="pub.id"
                  :user="pub.vendedor ? pub.vendedor.user : null"
                  :currentUserId="currentUserId"
                  :isOwner="pub.vendedor && pub.vendedor.user_id === currentUserId"
                  :estado="pub.estado"
                  :publicacion="pub"
                  :initialIsFavorite="true"
                />
              </div>
            </div>
            <div v-else class="text-gray-500 text-center py-12">
              No tienes publicaciones favoritas aún.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
