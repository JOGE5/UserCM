    <script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  chat: Object,
});

// Exponer `chat` como variable local para usar en el setup
const chat = props.chat;

const newMessage = ref('');
const messagesEnd = ref(null);
const processing = ref(false);

// Copia reactiva de los mensajes para poder mutarlos localmente
const messages = ref(chat.messages && Array.isArray(chat.messages) ? [...chat.messages] : []);

const sendMessage = () => {
    if (!newMessage.value.trim()) return;

    processing.value = true;
    axios.post(`/chats/${chat.id}/messages`, { contenido: newMessage.value })
        .then(response => {
            const data = response.data;
            // Añadir al array local de mensajes
            messages.value.push(data);
            newMessage.value = '';
            scrollToBottom();
        })
        .catch(error => {
            console.error('Error enviando mensaje:', error);
            if (error.response && error.response.data) {
                try {
                    const msg = typeof error.response.data === 'object' ? Object.values(error.response.data).flat().join('\n') : String(error.response.data);
                    alert('Error al enviar el mensaje:\n' + msg);
                } catch (e) {
                    alert('Error al enviar el mensaje.');
                }
            } else if (error.message) {
                alert('Error al enviar el mensaje: ' + error.message);
            } else {
                alert('Error al enviar el mensaje.');
            }
        })
        .finally(() => {
            processing.value = false;
        });
};

const scrollToBottom = () => {
    nextTick(() => {
        messagesEnd.value?.scrollIntoView({ behavior: 'smooth' });
    });
};

// Scroll al final al montar
nextTick(() => {
    scrollToBottom();
});

onMounted(() => {
  // Suscribirse a canal Echo si está disponible
  try {
  console.log('Mensajes/Show mounted - chat prop:', chat)
  console.log('Mensajes/Show initial messages length:', messages.value.length)
      if (window.Echo) {
      window.Echo.private(`chat.${chat.id}`)
        .listen('MessageSent', (e) => {
          // Evitar duplicados: si ya existe el mensaje, no añadir
          const exists = messages.value.some(m => m.id === e.message.id);
          if (!exists) {
            messages.value.push(e.message);
            scrollToBottom();
          }
        });
    } else {
      console.warn('Echo no está configurado. Habilita Laravel Echo + Pusher/laravel-websockets para mensajes en tiempo real.');
    }
  } catch (e) {
    console.error('Error suscribiéndose al canal Echo:', e);
  }
});

onBeforeUnmount(() => {
  try {
    if (window.Echo) {
      try { window.Echo.leave(`chat.${chat.id}`); } catch (e) {}
      try { window.Echo.leaveChannel(`chat.${chat.id}`); } catch (e) {}
    }
  } catch (e) {}
});
</script>

<template>
    <AppLayout :title="`Chat - ${chat.tipo === 'privado' ? 'Privado' : chat.nombre}`">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Chat
                </h2>
                <button @click="router.visit(route('mensajes.index'))" class="px-4 py-2 text-sm text-gray-600 bg-gray-200 rounded hover:bg-gray-300">
                    ← Volver a Mensajes
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium">
                            {{ chat.tipo === 'privado' ? 'Chat Privado' : chat.nombre }}
                        </h3>
                        <p class="mb-4 text-sm text-gray-600">
                            Participantes: {{ chat.users.map(u => u.name).join(', ') }}
                        </p>

                        <!-- Mensajes -->
                        <div class="p-4 mb-4 overflow-y-auto border rounded h-96 bg-gray-50">
                          <div v-if="messages && messages.length > 0">
                            <div v-for="message in messages" :key="message.id" class="mb-3">
                                    <div class="flex items-start space-x-2">
                                        <div class="flex-shrink-0">
                                            <div class="flex items-center justify-center w-8 h-8 text-sm font-medium text-white bg-blue-500 rounded-full">
                                                {{ message.sender.name.charAt(0).toUpperCase() }}
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-medium">{{ message.sender.name }}</span>
                                                <span class="text-xs text-gray-500">{{ new Date(message.created_at).toLocaleString() }}</span>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-800">{{ message.contenido }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center text-gray-500">
                                No hay mensajes aún. ¡Envía el primero!
                            </div>
                            <div ref="messagesEnd"></div>
                        </div>

                        <!-- From Uiverse.io by dorian_8749 -->
                        <div class="min-w-full p-4">
                          <div class="relative">
                            <div
                              class="relative flex flex-col bg-black border border-white/10 rounded-xl"
                            >
                              <div class="overflow-y-auto">
                                <textarea
                                  v-model="newMessage"
                                  rows="3"
                                  style="overflow: hidden; outline: none"
                                  class="w-full px-4 py-3 resize-none bg-transparent border-none focus:outline-none focus-visible:ring-0 focus-visible:ring-offset-0 placeholder:text-white/50 align-top leading-normal min-h-[80px] text-white"
                                  placeholder="Escribe un mensaje..."
                                  @keydown.enter.exact.prevent="sendMessage"
                                ></textarea>
                              </div>
                              <div class="h-14">
                                <div
                                  class="absolute flex items-center justify-between left-3 right-3 bottom-3"
                                >
                                  <div class="flex items-center gap-2">
                                    <button
                                      class="p-2 transition-colors border rounded-lg text-white/50 hover:text-white border-white/10 hover:border-white/20"
                                      aria-label="Attach file"
                                      type="button"
                                    >
                                      <svg
                                        class="w-4 h-4"
                                        stroke-linejoin="round"
                                        stroke-linecap="round"
                                        stroke-width="2"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        height="16"
                                        width="16"
                                        xmlns="http://www.w3.org/2000/svg"
                                      >
                                        <path
                                          d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"
                                        ></path>
                                      </svg>
                                    </button>
                                    <button
                                      class="p-2 transition-colors border rounded-lg text-white/50 hover:text-white border-white/10 hover:border-white/20"
                                      aria-label="Attach web link"
                                      type="button"
                                    >
                                      <svg
                                        class="w-4 h-4 text-blue-500"
                                        stroke-linejoin="round"
                                        stroke-linecap="round"
                                        stroke-width="2"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        height="16"
                                        width="16"
                                        xmlns="http://www.w3.org/2000/svg"
                                      >
                                        <circle r="10" cy="12" cx="12"></circle>
                                        <path
                                          d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"
                                        ></path>
                                        <path d="M2 12h20"></path>
                                      </svg>
                                    </button>
                                    <button
                                      class="p-2 transition-colors border rounded-lg text-white/50 hover:text-white border-white/10 hover:border-white/20"
                                      aria-label="Attach Figma link"
                                      type="button"
                                    >
                                      <svg
                                        class="w-4 h-4 text-pink-500"
                                        stroke-linejoin="round"
                                        stroke-linecap="round"
                                        stroke-width="2"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        height="16"
                                        width="16"
                                        xmlns="http://www.w3.org/2000/svg"
                                      >
                                        <path
                                          d="M5 5.5A3.5 3.5 0 0 1 8.5 2H12v7H8.5A3.5 3.5 0 0 1 5 5.5z"
                                        ></path>
                                        <path d="M12 2h3.5a3.5 3.5 0 1 1 0 7H12V2z"></path>
                                        <path d="M12 12.5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 1 1-7 0z"></path>
                                        <path
                                          d="M5 19.5A3.5 3.5 0 0 1 8.5 16H12v3.5a3.5 3.5 0 1 1-7 0z"
                                        ></path>
                                        <path
                                          d="M5 12.5A3.5 3.5 0 0 1 8.5 9H12v7H8.5A3.5 3.5 0 0 1 5 12.5z"
                                        ></path>
                                      </svg>
                                    </button>
                                  </div>
                                  <button
                                    @click="sendMessage"
                                    :disabled="processing"
                                    :class="processing ? 'opacity-60 cursor-not-allowed' : ''"
                                    class="p-2 text-blue-500 transition-colors hover:text-blue-600"
                                    aria-label="Send message"
                                    type="button"
                                  >
                                    <svg
                                      class="w-6 h-6"
                                      stroke-linejoin="round"
                                      stroke-linecap="round"
                                      stroke-width="2"
                                      stroke="currentColor"
                                      fill="none"
                                      viewBox="0 0 24 24"
                                      height="24"
                                      width="24"
                                      xmlns="http://www.w3.org/2000/svg"
                                    >
                                      <circle r="10" cy="12" cx="12"></circle>
                                      <path d="m16 12-4-4-4 4"></path>
                                      <path d="M12 16V8"></path>
                                    </svg>
                                  </button>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
