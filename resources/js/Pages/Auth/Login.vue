<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import { Mail, Lock, Eye, EyeOff } from 'lucide-vue-next';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Iniciar Sesión | Campus Market" />

    <div class="relative flex flex-col items-center justify-center min-h-screen px-4 overflow-hidden bg-black sm:px-6 lg:px-8">
        <!-- Video de fondo oscuro -->
        <video autoplay muted loop playsinline class="absolute inset-0 z-0 object-cover w-full h-full opacity-60">
            <source src="/videos/Waza21.mp4" type="video/mp4" />
        </video>
        
        <!-- Capas de sombras y gradientes para efecto premium (referencia Welcome.vue) -->
        <div class="absolute inset-0 z-0 bg-gradient-to-t from-black via-black/60 to-black/40"></div>
        <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_center,transparent_0%,rgba(0,0,0,0.8)_100%)]"></div>
        
        <!-- Luces de fondo (Aurora) -->
        <div class="absolute z-0 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-indigo-600/20 rounded-full blur-[100px] mix-blend-screen opacity-50 pointer-events-none"></div>

        <div v-if="status" class="relative z-20 w-full max-w-md p-4 mb-6 text-sm font-medium text-emerald-400 border rounded-xl bg-emerald-500/10 border-emerald-500/20 backdrop-blur-md">
            {{ status }}
        </div>

        <!-- Tarjeta Glassmorphism -->
        <div class="relative z-20 w-full max-w-md p-8 sm:p-10 transition-all duration-500 bg-white/5 shadow-[0_0_40px_rgba(0,0,0,0.5)] rounded-[2.5rem] border border-white/10 backdrop-blur-xl group hover:border-white/20">
            
            <div class="flex flex-col items-center mb-10 text-center">
                <Link href="/" class="flex items-center justify-center w-20 h-20 mb-6 transition-transform duration-500 overflow-hidden rounded-[1.5rem] bg-white/10 shadow-[0_0_20px_rgba(255,255,255,0.05)] hover:scale-110">
                    <img src="/images/posters/logo-team.png" alt="Logo" class="object-cover w-full h-full p-2" />
                </Link>
                <h2 class="text-3xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-indigo-300">
                    Bienvenido de vuelta
                </h2>
                <p class="mt-3 text-sm font-medium text-gray-400">Ingresa tus datos para continuar</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Campo Email -->
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-medium text-gray-300 ml-1">Correo Electrónico</label>
                    <div class="relative group/input">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <Mail class="w-5 h-5 text-gray-500 transition-colors group-focus-within/input:text-indigo-400" />
                        </div>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="w-full py-3.5 pl-12 pr-4 text-white transition-all duration-300 bg-black/40 border border-white/10 rounded-2xl placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 backdrop-blur-sm"
                            placeholder="tu@correo.com"
                            required
                            autofocus
                            autocomplete="username"
                        />
                    </div>
                    <InputError class="mt-1 text-xs text-red-500" :message="form.errors.email" />
                </div>

                <!-- Campo Contraseña -->
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-medium text-gray-300 ml-1">Contraseña</label>
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
                            autocomplete="current-password"
                        />
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-gray-300 focus:outline-none transition-colors">
                            <Eye v-if="!showPassword" class="w-5 h-5" />
                            <EyeOff v-else class="w-5 h-5" />
                        </button>
                    </div>
                    <InputError class="mt-1 text-xs text-red-500" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer group">
                        <div class="relative flex items-center justify-center w-5 h-5 bg-black/40 border border-white/20 rounded transition-colors group-hover:border-indigo-400">
                            <input type="checkbox" id="remember" v-model="form.remember" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer peer" />
                            <svg class="w-3.5 h-3.5 text-indigo-400 opacity-0 transition-opacity peer-checked:opacity-100 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 5L5 9L13 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-400 transition-colors group-hover:text-gray-300">Recordarme</span>
                    </label>

                    <Link v-if="canResetPassword" :href="route('password.request.custom')" class="text-sm font-medium text-indigo-400 transition-colors hover:text-indigo-300 hover:underline">
                        ¿Olvidaste tu contraseña?
                    </Link>
                </div>

                <button type="submit" :disabled="form.processing" class="relative flex items-center justify-center w-full h-14 overflow-hidden font-bold text-white transition-all bg-indigo-600 rounded-2xl group hover:bg-indigo-500 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 mt-8 shadow-[0_0_20px_rgba(79,70,229,0.3)] hover:shadow-[0_0_40px_rgba(79,70,229,0.5)]">
                    <span class="relative z-10">Ingresar al Sistema</span>
                    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-white/20 to-transparent"></div>
                </button>

                <!-- Separador -->
                <div class="relative flex items-center justify-center mt-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-white/10"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 text-gray-500 bg-[#0c0c0c] rounded-full">O continuar con</span>
                    </div>
                </div>

                <!-- Botón de Google -->
                <a :href="route('google.login')" class="relative flex items-center justify-center w-full h-14 mt-6 overflow-hidden font-bold text-white transition-all bg-white/5 border border-white/10 rounded-2xl group hover:bg-white/10 hover:border-white/20 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    <span>Google</span>
                </a>

                <p class="text-center text-sm font-medium text-gray-400 mt-8">
                    ¿Todavía no tienes una cuenta? 
                    <Link :href="route('register')" class="text-indigo-400 transition-colors hover:text-indigo-300 hover:underline">Regístrate</Link>
                </p>
            </form>
        </div>
    </div>
</template>

<style scoped>
</style>
