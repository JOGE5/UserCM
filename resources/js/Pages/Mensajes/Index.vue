<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    chats: Array,
});

// Debug: mostrar en consola los chats recibidos
console.log('Mensajes/Index props.chats:', typeof chats !== 'undefined' ? chats : 'undefined');
</script>

<template>
    <AppLayout title="Mensajes">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Mensajes
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="mb-4 text-lg font-medium">Tus Chats</h3>
                        <div v-if="chats && chats.length > 0" class="space-y-4">
                            <div v-for="chat in chats" :key="chat.id" class="p-4 border rounded-lg">
                                <Link :href="route('chats.show', chat.id)" class="block">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-semibold">{{ chat.tipo === 'privado' ? 'Chat Privado' : chat.nombre }}</h4>
                                            <p class="text-sm text-gray-600">
                                                Participantes: {{ chat.users.map(u => u.name).join(', ') }}
                                            </p>
                                            <p v-if="chat.messages && chat.messages.length > 0" class="text-sm text-gray-500">
                                                Último mensaje: {{ chat.messages[chat.messages.length - 1].contenido.substring(0, 50) }}...
                                            </p>
                                        </div>
                                        <span class="text-gray-400">→</span>
                                    </div>
                                </Link>
                            </div>
                        </div>
                        <div v-else>
                            <p>No tienes chats aún. ¡Contacta a alguien desde las publicaciones!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
