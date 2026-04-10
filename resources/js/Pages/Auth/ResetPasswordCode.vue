<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import { Lock, Eye, EyeOff } from 'lucide-vue-next';

const props = defineProps({
    email: String,
});

const form = useForm({
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('password.store.custom'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Nueva Contraseña | Campus Market" />

    <div class="relative flex flex-col items-center justify-center min-h-screen px-4 overflow-hidden bg-black sm:px-6 lg:px-8">
        <video autoplay muted loop playsinline class="absolute inset-0 z-0 object-cover w-full h-full opacity-60">
            <source src="/videos/Waza21.mp4" type="video/mp4" />
        </video>
        
        <div class="absolute inset-0 z-0 bg-gradient-to-t from-black via-black/60 to-black/40"></div>
        <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_center,transparent_0%,rgba(0,0,0,0.8)_100%)]"></div>
        <div class="absolute z-0 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-indigo-600/20 rounded-full blur-[100px] mix-blend-screen opacity-50 pointer-events-none"></div>

        <div class="relative z-20 w-full max-w-md p-8 sm:p-10 transition-all duration-500 bg-white/5 shadow-[0_0_40px_rgba(0,0,0,0.5)] rounded-[2.5rem] border border-white/10 backdrop-blur-xl group hover:border-white/20">
            <div class="flex flex-col items-center mb-8 text-center">
                <Link href="/" class="flex items-center justify-center w-20 h-20 mb-6 transition-transform duration-500 overflow-hidden rounded-[1.5rem] bg-white/10 shadow-[0_0_20px_rgba(255,255,255,0.05)] hover:scale-110">
                    <img src="/images/posters/logo-team.png" alt="Logo" class="object-cover w-full h-full p-2" />
                </Link>
                <h2 class="text-3xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-indigo-300">
                    Nueva Contraseña
                </h2>
                <p class="mt-3 text-sm font-medium text-gray-400">Ingresa tu nueva contraseña para <span class="text-white">{{ email }}</span>.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Campo Contraseña -->
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-medium text-gray-300 ml-1">Nueva Contraseña</label>
                    <div class="relative group/input">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <Lock class="w-5 h-5 text-gray-500 transition-colors group-focus-within/input:text-indigo-400" />
                        </div>
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="w-full py-3.5 pl-12 pr-12 text-white transition-all duration-300 bg-black/40 border border-white/10 rounded-2xl placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 backdrop-blur-sm"
                            placeholder="••••••••"
                            required
                            autofocus
                        />
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-gray-300 focus:outline-none transition-colors">
                            <Eye v-if="!showPassword" class="w-5 h-5" />
                            <EyeOff v-else class="w-5 h-5" />
                        </button>
                    </div>
                    <InputError class="mt-1 text-xs text-red-500" :message="form.errors.password" />
                </div>

                <!-- Campo Confirmación -->
                <div class="space-y-2">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300 ml-1">Confirmar Contraseña</label>
                    <div class="relative group/input">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <Lock class="w-5 h-5 text-gray-500 transition-colors group-focus-within/input:text-indigo-400" />
                        </div>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            class="w-full py-3.5 pl-12 pr-12 text-white transition-all duration-300 bg-black/40 border border-white/10 rounded-2xl placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 backdrop-blur-sm"
                            placeholder="••••••••"
                            required
                        />
                        <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-gray-300 focus:outline-none transition-colors">
                            <Eye v-if="!showPasswordConfirmation" class="w-5 h-5" />
                            <EyeOff v-else class="w-5 h-5" />
                        </button>
                    </div>
                    <InputError class="mt-1 text-xs text-red-500" :message="form.errors.password_confirmation" />
                </div>

                <button type="submit" :disabled="form.processing" class="relative flex items-center justify-center w-full h-14 overflow-hidden font-bold text-white transition-all bg-indigo-600 rounded-2xl group hover:bg-indigo-500 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 mt-6 shadow-[0_0_20px_rgba(79,70,229,0.3)] hover:shadow-[0_0_40px_rgba(79,70,229,0.5)]">
                    <span class="relative z-10">Restablecer Contraseña</span>
                    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-white/20 to-transparent"></div>
                </button>
            </form>
        </div>
    </div>
</template>
