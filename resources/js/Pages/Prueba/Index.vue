<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Prueba">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Prueba
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p>Esta es la página de prueba</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
