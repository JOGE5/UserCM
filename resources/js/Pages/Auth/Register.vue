<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Registro | Campus Market" />

    <div class="relative flex flex-col items-center justify-center min-h-screen px-4 py-10 overflow-hidden bg-black sm:px-6 lg:px-8">
        <!-- Video de fondo oscuro -->
        <video autoplay muted loop playsinline class="absolute inset-0 z-0 object-cover w-full h-full opacity-60">
            <source src="/videos/Coder.mp4" type="video/mp4" />
            Tu navegador no soporta el video.
        </video>
        
        <!-- Capas de sombras y gradientes para efecto premium (referencia Welcome.vue) -->
        <div class="absolute inset-0 z-0 bg-gradient-to-t from-black via-black/60 to-black/40"></div>
        <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_center,transparent_0%,rgba(0,0,0,0.8)_100%)]"></div>
        
        <!-- Luces de fondo (Aurora) -->
        <div class="absolute z-0 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-indigo-600/20 rounded-full blur-[100px] mix-blend-screen opacity-50 pointer-events-none"></div>

        <!-- Tarjeta Glassmorphism -->
        <div class="relative z-20 w-full max-w-md p-8 sm:p-10 transition-all duration-500 bg-white/5 shadow-[0_0_40px_rgba(0,0,0,0.5)] rounded-[2.5rem] border border-white/10 backdrop-blur-xl group hover:border-white/20">
            
            <div class="flex flex-col items-center mb-10 text-center">
                <Link href="/" class="flex items-center justify-center w-20 h-20 mb-6 transition-transform duration-500 overflow-hidden rounded-[1.5rem] bg-white/10 shadow-[0_0_20px_rgba(255,255,255,0.05)] hover:scale-110">
                    <img src="/images/posters/logo-team.png" alt="Logo" class="object-cover w-full h-full p-2" />
                </Link>
                <h2 class="text-3xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-indigo-300">
                    Crea tu Cuenta
                </h2>
                <p class="mt-3 text-sm font-medium text-gray-400">Únete a la comunidad universitaria</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Campo Nombre -->
                <div class="space-y-2">
                    <label for="name" class="block text-sm font-medium text-gray-300 ml-1">Nombre Completo</label>
                    <div class="relative group/input">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500 transition-colors group-focus-within/input:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full py-3.5 pl-12 pr-4 text-white transition-all duration-300 bg-black/40 border border-white/10 rounded-2xl placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 backdrop-blur-sm"
                            placeholder="Ej. Juan Pérez"
                            required
                            autofocus
                            autocomplete="name"
                        />
                    </div>
                    <InputError class="mt-1 text-xs text-red-500" :message="form.errors.name" />
                </div>

                <!-- Campo Email -->
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-medium text-gray-300 ml-1">Correo Electrónico</label>
                    <div class="relative group/input">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500 transition-colors group-focus-within/input:text-indigo-400" viewBox="0 0 32 32" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path d="m30.853 13.87a15 15 0 0 0 -29.729 4.082 15.1 15.1 0 0 0 12.876 12.918 15.6 15.6 0 0 0 2.016 .13 14.85 14.85 0 0 0 7.715-2.145 1 1 0 1 0 -1.031-1.711 13.007 13.007 0 1 1 5.458-6.529 2.149 2.149 0 0 1 -4.158-.759v-10.856a1 1 0 0 0 -2 0v1.726a8 8 0 1 0 .2 10.325 4.135 4.135 0 0 0 7.83 .274 15.2 15.2 0 0 0 .823-7.455zm-14.853 8.13a6 6 0 1 1 6-6 6.006 6.006 0 0 1 -6 6z"></path>
                            </svg>
                        </div>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="w-full py-3.5 pl-12 pr-4 text-white transition-all duration-300 bg-black/40 border border-white/10 rounded-2xl placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 backdrop-blur-sm"
                            placeholder="tu@correo.com"
                            required
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
                            <svg class="w-5 h-5 text-gray-500 transition-colors group-focus-within/input:text-indigo-400" viewBox="-64 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path d="m336 512h-288c-26.453125 0-48-21.523438-48-48v-224c0-26.476562 21.546875-48 48-48h288c26.453125 0 48 21.523438 48 48v224c0 26.476562-21.546875 48-48 48zm-288-288c-8.8125 0-16 7.167969-16 16v224c0 8.832031 7.1875 16 16 16h288c8.8125 0 16-7.167969 16-16v-224c0-8.832031-7.1875-16-16-16zm0 0"></path>
                                <path d="m304 224c-8.832031 0-16-7.167969-16-16v-80c0-52.929688-43.070312-96-96-96s-96 43.070312-96 96v80c0 8.832031-7.167969 16-16 16s-16-7.167969-16-16v-80c0-70.59375 57.40625-128 128-128s128 57.40625 128 128v80c0 8.832031-7.167969 16-16 16zm0 0"></path>
                            </svg>
                        </div>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="w-full py-3.5 pl-12 pr-4 text-white transition-all duration-300 bg-black/40 border border-white/10 rounded-2xl placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 backdrop-blur-sm"
                            placeholder="Mínimo 8 caracteres"
                            required
                            autocomplete="new-password"
                        />
                    </div>
                    <InputError class="mt-1 text-xs text-red-500" :message="form.errors.password" />
                </div>

                <!-- Campo Confirmar Contraseña -->
                <div class="space-y-2">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300 ml-1">Confirmar Contraseña</label>
                    <div class="relative group/input">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500 transition-colors group-focus-within/input:text-indigo-400" viewBox="-64 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path d="m336 512h-288c-26.453125 0-48-21.523438-48-48v-224c0-26.476562 21.546875-48 48-48h288c26.453125 0 48 21.523438 48 48v224c0 26.476562-21.546875 48-48 48zm-288-288c-8.8125 0-16 7.167969-16 16v224c0 8.832031 7.1875 16 16 16h288c8.8125 0 16-7.167969 16-16v-224c0-8.832031-7.1875-16-16-16zm0 0"></path>
                                <path d="m304 224c-8.832031 0-16-7.167969-16-16v-80c0-52.929688-43.070312-96-96-96s-96 43.070312-96 96v80c0 8.832031-7.167969 16-16 16s-16-7.167969-16-16v-80c0-70.59375 57.40625-128 128-128s128 57.40625 128 128v80c0 8.832031-7.167969 16-16 16zm0 0"></path>
                            </svg>
                        </div>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="w-full py-3.5 pl-12 pr-4 text-white transition-all duration-300 bg-black/40 border border-white/10 rounded-2xl placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 backdrop-blur-sm"
                            placeholder="Repite tu contraseña"
                            required
                            autocomplete="new-password"
                        />
                    </div>
                    <InputError class="mt-1 text-xs text-red-500" :message="form.errors.password_confirmation" />
                </div>

                <div v-if="$page.props.jetstream?.hasTermsAndPrivacyPolicyFeature" class="pt-2">
                    <label class="flex items-start cursor-pointer group">
                        <div class="relative flex items-center justify-center w-5 h-5 mt-0.5 bg-black/40 border border-white/20 rounded transition-colors group-hover:border-indigo-400 shrink-0">
                            <input type="checkbox" v-model="form.terms" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer peer" />
                            <svg class="w-3.5 h-3.5 text-indigo-400 opacity-0 transition-opacity peer-checked:opacity-100 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 5L5 9L13 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-400 transition-colors group-hover:text-gray-300">
                            Acepto los <a target="_blank" :href="route('terms.show')" class="text-indigo-400 hover:text-indigo-300 hover:underline">Términos y condiciones</a> y la <a target="_blank" :href="route('policy.show')" class="text-indigo-400 hover:text-indigo-300 hover:underline">Política de Privacidad</a>
                        </span>
                    </label>
                    <InputError class="mt-2 text-xs text-red-500" :message="form.errors.terms" />
                </div>

                <button type="submit" :disabled="form.processing" class="relative flex items-center justify-center w-full h-14 overflow-hidden font-bold text-white transition-all bg-indigo-600 rounded-2xl group hover:bg-indigo-500 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 mt-8 shadow-[0_0_20px_rgba(79,70,229,0.3)] hover:shadow-[0_0_40px_rgba(79,70,229,0.5)]">
                    <span class="relative z-10">Crear Cuenta</span>
                    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-white/20 to-transparent"></div>
                </button>

                <p class="text-center text-sm font-medium text-gray-400 mt-8">
                    ¿Ya tienes una cuenta? 
                    <Link :href="route('login')" class="text-indigo-400 transition-colors hover:text-indigo-300 hover:underline">Inicia Sesión</Link>
                </p>
            </form>
        </div>
    </div>
</template>

<style scoped>
/* Eliminar clases inecesarias o agregar scoped CSS si se ocupara */
</style>
