<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, EffectFade } from 'swiper/modules';
import 'swiper/swiper-bundle.css';
import { ShoppingBag, BookOpen, Users, Zap, Shield, ChevronRight, Search, Sparkles } from 'lucide-vue-next';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});

const modules = [Autoplay, EffectFade];
const videoRefs = ref([]);
const currentSlide = ref(0);
let swiperInstance = null;
const showIntro = ref(true);
const scrollY = ref(0);
const isScrolledNav = ref(false);

const handleScroll = () => {
    scrollY.value = window.scrollY;
    isScrolledNav.value = window.scrollY > 50;
};

const videos = [
    {
        src: '/videos/Clip1.mp4',
        poster: '/images/posters/poster1.jpg',
        title: 'Innovación',
        description: 'Un ecosistema creado exclusivamente para el entorno universitario moderno.'
    },
    {
        src: '/videos/Clip2.mp4',
        poster: '/images/posters/poster2.jpg',
        title: 'Impulsando tu Carrera',
        description: 'Encuentra las herramientas, plataformas y bibliografía para potenciar tus estudios.'
    },
    {
        src: '/videos/Clip3.mp4',
        poster: '/images/posters/poster3.jpg',
        title: 'Intercambio Seguro',
        description: 'Todas las transacciones protegidas con reputación y validación entre estudiantes.'
    }
];

const onSwiper = (swiper) => {
    swiperInstance = swiper;
    playCurrentVideo();
};

const onSlideChange = (swiper) => {
    currentSlide.value = swiper.realIndex;
    playCurrentVideo();
};

const playCurrentVideo = () => {
    const video = videoRefs.value[currentSlide.value];
    if (video) {
        video.currentTime = 0;
        video.play().catch(err => console.log('Autoplay prevented:', err));
    }
};

const handleVideoEnd = (index) => {
    if (index === currentSlide.value && swiperInstance) {
        swiperInstance.slideNext();
    }
};

onMounted(() => {
    videoRefs.value.forEach((video, index) => {
        if (video) {
            video.addEventListener('ended', () => handleVideoEnd(index));
        }
    });

    setTimeout(() => { showIntro.value = false; }, 3000);

    setTimeout(() => {
        if (videoRefs.value[0]) {
            videoRefs.value[0].play().catch(err => console.log('Autoplay prevented:', err));
        }
    }, 3300);

    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    videoRefs.value.forEach((video, index) => {
        if (video) {
            video.removeEventListener('ended', () => handleVideoEnd(index));
        }
    });
    window.removeEventListener('scroll', handleScroll);
});

const features = [
    { name: 'Mercado Vital', description: 'Compra y vende artículos académicos al instante con tu comunidad estudiantil.', icon: ShoppingBag, color: 'text-emerald-400 bg-emerald-400/10 border-emerald-400/20' },
    { name: 'Biblioteca Colaborativa', description: 'Accede a un océano de conocimiento intercambiando libros y apuntes.', icon: BookOpen, color: 'text-blue-400 bg-blue-400/10 border-blue-400/20' },
    { name: 'Red Universitaria', description: 'Forja alianzas, descubre eventos y conoce a talentos de otras facultades.', icon: Users, color: 'text-fuchsia-400 bg-fuchsia-400/10 border-fuchsia-400/20' },
    { name: 'Entorno Verificado', description: 'Sistema de protección e identidad que asegura un trato justo y transparente.', icon: Shield, color: 'text-amber-400 bg-amber-400/10 border-amber-400/20' },
];
</script>

<template>
    <Head title="Bienvenido | Campus Market" />

    <!-- Intro Premium Oscuro -->
    <div v-if="showIntro" class="fixed inset-0 z-[100] flex flex-col items-center justify-center transition-opacity ease-in-out duration-1000 bg-black">
        <div class="absolute inset-0 z-0 bg-center bg-cover bg-no-repeat opacity-20 bg-[url('https://images.unsplash.com/photo-1550684848-fac1c5b4e853?q=80&w=2070&auto=format&fit=crop')] mix-blend-screen"></div>
        <div class="relative z-10 flex flex-col items-center">
            <div class="flex items-center justify-center mb-10 w-28 h-28 bg-white/5 backdrop-blur-2xl rounded-full border border-white/10 shadow-[0_0_80px_rgba(139,92,246,0.3)] animate-pulse-logo">
                <Sparkles class="w-12 h-12 text-white/90" />
            </div>
            <h1 class="mb-3 text-5xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-br from-white via-gray-300 to-gray-500 animate-slide-up opacity-0" style="animation-fill-mode: forwards; animation-delay: 0.2s;">
                Campus Market
            </h1>
            <p class="text-xl font-light tracking-wide text-gray-400 animate-slide-up opacity-0" style="animation-fill-mode: forwards; animation-delay: 0.5s;">
                Tu ecosistema. Elevado.
            </p>
        </div>
    </div>

    <!-- Barra de Navegación Glassmorphism Premium -->
    <nav :class="[ 
        'fixed top-0 inset-x-0 z-50 transition-all duration-500 ease-out border-b py-4',
        isScrolledNav ? 'bg-black/80 backdrop-blur-xl border-white/10 shadow-2xl' : 'bg-gradient-to-b from-black/80 to-transparent border-transparent py-6'
    ]">
        <div class="container px-6 mx-auto lg:max-w-7xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-[0_0_20px_rgba(99,102,241,0.4)]">
                    <Sparkles class="w-5 h-5 text-white" />
                </div>
                <span class="text-xl font-bold tracking-tighter text-white">
                    Campus<span class="text-indigo-400">Market</span>
                </span>
            </div>

            <div v-if="canLogin" class="flex gap-4 items-center">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="px-6 py-2.5 text-sm font-semibold tracking-wide text-white transition-all rounded-full bg-white/10 hover:bg-white/20 border border-white/5 backdrop-blur-md"
                >
                    Mi Panel 
                </Link>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="hidden sm:block px-4 py-2 text-sm font-medium text-gray-300 transition-colors hover:text-white"
                    >
                        Ingresar
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="group relative inline-flex h-10 items-center justify-center overflow-hidden rounded-full bg-indigo-600 px-6 font-medium text-neutral-50 shadow-[0_0_40px_rgba(79,70,229,0.3)] transition-all hover:scale-105 hover:bg-indigo-500 hover:shadow-[0_0_60px_rgba(79,70,229,0.5)]"
                    >
                        <span class="relative z-10 flex items-center gap-2">
                            Crear Cuenta
                            <ChevronRight class="w-4 h-4 transition-transform group-hover:translate-x-1" />
                        </span>
                        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-white/20 to-transparent"></div>
                    </Link>
                </template>
            </div>
        </div>
    </nav>

    <!-- Sección Hero de Alto Impacto -->
    <Transition name="smooth-reveal">
        <div v-show="!showIntro" class="relative h-screen min-h-[700px] overflow-hidden bg-black">
            
            <Swiper
                :modules="modules"
                :slides-per-view="1"
                :space-between="0"
                :loop="true"
                :effect="'fade'"
                :speed="2000"
                :allowTouchMove="false"
                @swiper="onSwiper"
                @slideChange="onSlideChange"
                class="absolute inset-0 w-full h-full"
            >
                <SwiperSlide v-for="(video, index) in videos" :key="index">
                    <div class="relative w-full h-full">
                        <video
                            :ref="el => videoRefs[index] = el"
                            class="absolute inset-0 object-cover w-full h-full"
                            :style="{ transform: `scale(${1 + scrollY * 0.0004})` }"
                            muted
                            playsinline
                            :poster="video.poster"
                            preload="auto"
                        >
                            <source :src="video.src" type="video/mp4">
                        </video>
                        <!-- Sombras ultra HD superpuestas en el video para texto legible -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-black/30"></div>
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,transparent_0%,rgba(0,0,0,0.8)_100%)]"></div>
                        
                        <!-- Contenido Hero Centrado -->
                        <div class="absolute inset-0 flex items-center justify-center p-6">
                            <div class="max-w-4xl text-center z-10" :style="{ transform: `translateY(${scrollY * 0.15}px)` }">
                                <div class="inline-flex items-center gap-2 px-4 py-1.5 mb-8 text-xs font-semibold tracking-widest text-indigo-300 uppercase bg-indigo-500/10 border border-indigo-500/20 rounded-full backdrop-blur-md">
                                    <span class="w-2 h-2 bg-indigo-400 rounded-full animate-pulse"></span>
                                    La Siguiente Generación
                                </div>
                                <h2 class="mb-6 text-6xl font-black tracking-tighter text-white md:text-8xl drop-shadow-2xl">
                                    {{ video.title }}
                                </h2>
                                <p class="max-w-2xl mx-auto mb-12 text-xl font-light leading-relaxed text-gray-300 md:text-2xl">
                                    {{ video.description }}
                                </p>
                                <div class="flex flex-col items-center justify-center gap-5 sm:flex-row">
                                    <a href="#explorar" class="group relative flex h-14 items-center justify-center gap-2 overflow-hidden rounded-full bg-white px-8 font-bold text-black transition-transform hover:scale-105">
                                        Explorar Entorno
                                        <Search class="w-5 h-5 transition-transform group-hover:rotate-12" />
                                    </a>
                                    <Link
                                        v-if="!$page.props.auth.user"
                                        :href="route('register')"
                                        class="flex h-14 items-center justify-center gap-2 rounded-full border border-white/20 bg-white/5 px-8 font-semibold text-white backdrop-blur-lg transition-all hover:bg-white/10 hover:border-white/40"
                                    >
                                        Comenzar Ahora
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </SwiperSlide>
            </Swiper>

            <!-- Barra inferior e indicadores de Slider -->
            <div class="absolute inset-x-0 bottom-0 z-30 h-32 bg-gradient-to-t from-black to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-0 bottom-10 z-40 flex flex-col items-center justify-center gap-6">
                <div class="flex gap-4">
                    <button
                        v-for="(_, idx) in videos"
                        :key="idx"
                        @click="swiperInstance?.slideTo(idx)"
                        :class="[   
                            'h-1 rounded-full transition-all duration-500',
                            currentSlide === idx ? 'w-16 bg-indigo-500 shadow-[0_0_10px_rgba(99,102,241,0.8)]' : 'w-6 bg-white/20 hover:bg-white/40'
                        ]"
                    ></button>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Sección Oscura Principal (Características) -->
    <div id="explorar" class="relative z-10 bg-black text-white">
        <!-- Textura / Grilla de fondo -->
        <div class="absolute inset-0 z-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik02MCAwaC0yVjYwSDYweiIgZmlsbD0iI2ZmZiIgZmlsbC1vcGFjaXR5PSIwLjAyIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiLz4KPC9zdmc+')] opacity-50"></div>
        <div class="absolute top-0 inset-x-0 h-[500px] bg-gradient-to-b from-black to-transparent pointer-events-none"></div>
        
        <div class="relative z-10 py-32 sm:py-40">
            <div class="px-6 mx-auto max-w-7xl lg:px-8">
                
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-sm font-semibold tracking-widest text-indigo-400 uppercase">Perfección Tecnológica</h2>
                    <p class="mt-4 text-4xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl text-gradient bg-clip-text text-transparent bg-gradient-to-r from-white via-gray-200 to-gray-500">
                        Diseñado para tu éxito.
                    </p>
                    <p class="mt-6 text-lg tracking-wide text-gray-400">
                        Un mercado estudiantil no tiene por qué ser aburrido. Hemos reinventado la manera en la que compras, intercambias libros y conectas con otros estudiantes. Bienvenido a la modernidad.
                    </p>
                </div>

                <div class="mx-auto mt-20 max-w-7xl sm:mt-24 lg:mt-32">
                    <dl class="grid max-w-xl grid-cols-1 gap-8 mx-auto lg:max-w-none lg:grid-cols-4">
                        <div v-for="feature in features" :key="feature.name" 
                            class="group relative flex flex-col items-center p-8 text-center transition-all duration-500 bg-white/5 border border-white/5 rounded-[2rem] hover:bg-white/10 hover:border-white/20 hover:-translate-y-2 overflow-hidden backdrop-blur-sm">
                            <!-- Destello de fondo en hover -->
                            <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                            
                            <dt class="flex flex-col items-center gap-y-5 z-10">
                                <div :class="['flex items-center justify-center w-20 h-20 rounded-[1.5rem] border transition-transform group-hover:scale-110 duration-500', feature.color]">
                                    <component :is="feature.icon" class="w-10 h-10" aria-hidden="true" />
                                </div>
                                <h3 class="text-2xl font-bold text-white tracking-tight">
                                    {{ feature.name }}
                                </h3>
                            </dt>
                            <dd class="mt-4 text-base leading-relaxed text-gray-400 z-10">
                                {{ feature.description }}
                            </dd>
                        </div>
                    </dl>
                </div>

            </div>
        </div>

        <!-- Banner de Llamada a la Acción (CTA) Espectacular -->
        <div class="relative py-24 overflow-hidden border-t sm:py-32 border-white/10">
            <div class="absolute inset-0 -z-10 bg-black"></div>
            <!-- Luces Aurora -->
            <div class="absolute -z-10 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-600/30 rounded-full blur-[120px] mix-blend-screen opacity-50"></div>
            <div class="absolute -z-10 top-1/2 left-1/4 -translate-y-1/2 w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[100px] mix-blend-screen opacity-40"></div>
            
            <div class="px-6 mx-auto max-w-7xl lg:px-8 relative z-10">
                <div class="max-w-3xl mx-auto text-center bg-black/40 backdrop-blur-2xl border border-white/10 rounded-[3rem] p-12 lg:p-20 shadow-[0_0_100px_rgba(0,0,0,0.5)]">
                    <h2 class="text-4xl font-black tracking-tighter text-white sm:text-6xl">
                        Es hora de <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400">evolucionar</span>.
                    </h2>
                    <p class="mt-6 text-xl text-gray-300">
                        Súmate a la experiencia inmersiva del mercado estudiantil definitivo. Rápido, seguro y diseñado con pasión.
                    </p>
                    <div class="flex flex-col items-center justify-center gap-4 mt-10 sm:flex-row">
                        <Link :href="route('register')" class="w-full sm:w-auto px-10 py-4 text-lg font-bold text-black transition-all bg-white rounded-full hover:bg-gray-200 hover:scale-105 shadow-[0_0_40px_rgba(255,255,255,0.2)]">
                            Crear Cuenta Gratis
                        </Link>
                        <Link :href="route('login')" class="group flex items-center justify-center gap-2 w-full sm:w-auto px-10 py-4 text-lg font-bold text-white transition-all bg-white/5 border border-white/10 rounded-full hover:bg-white/10">
                            Iniciar Sesión
                            <ChevronRight class="w-5 h-5 transition-transform group-hover:translate-x-1" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Dark Premium -->
        <footer class="border-t border-white/10 bg-black">
            <div class="px-6 py-16 mx-auto max-w-7xl md:flex md:items-center md:justify-between lg:px-8">
                <div class="flex justify-center md:justify-start">
                    <div class="flex items-center gap-3">
                        <Sparkles class="w-6 h-6 text-indigo-500" />
                        <span class="text-xl font-bold tracking-tighter text-white">CampusMarket</span>
                    </div>
                </div>
                <div class="mt-8 flex flex-col items-center md:items-end md:mt-0">
                    <p class="text-sm text-gray-500 font-medium">
                        &copy; {{ new Date().getFullYear() }} Proyectos Universitarios. Todos los derechos reservados.
                    </p>
                    <div class="flex items-center gap-4 mt-3 text-xs font-mono text-gray-600">
                        <span>Laravel v{{ laravelVersion }}</span>
                        <span class="w-1 h-1 bg-gray-700 rounded-full"></span>
                        <span>PHP v{{ phpVersion }}</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
/* Base Dark Premium */
html {
    scroll-behavior: smooth;
    background-color: #000;
}

body {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    background-color: #000;
    color: #fff;
}

.smooth-reveal-enter-active, .smooth-reveal-leave-active {
    transition: filter 1s ease-in-out, opacity 1s ease-in-out;
}
.smooth-reveal-enter-from, .smooth-reveal-leave-to {
    opacity: 0;
    filter: brightness(0);
}
.smooth-reveal-enter-to, .smooth-reveal-leave-from {
    opacity: 1;
    filter: brightness(1);
}

@keyframes pulse-logo {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.8; }
}

@keyframes slide-up {
    0% { transform: translateY(30px); opacity: 0; }
    100% { transform: translateY(0); opacity: 1; }
}

.animate-pulse-logo {
    animation: pulse-logo 3s ease-in-out infinite;
}

.animate-slide-up {
    animation: slide-up 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
