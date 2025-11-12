<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, EffectFade } from 'swiper/modules';
import 'swiper/swiper-bundle.css';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const modules = [Autoplay, EffectFade];
const videoRefs = ref([]);
const currentSlide = ref(0);
let swiperInstance = null;
const showIntro = ref(true);
const scrollY = ref(0);

const handleScroll = () => {
    scrollY.value = window.scrollY;
};

// Configura tus videos aquí - solo cambia los nombres de archivo
const videos = [
    {
        src: '/videos/Clip1.mp4',
        poster: '/images/posters/poster1.jpg',
        title: 'Innovación',
        description: 'Nuevos metodos'
    },
    {
        src: '/videos/Clip2.mp4',
        poster: '/images/posters/poster2.jpg',
        title: 'Comunidad',
        description: 'Crecimiento conjunto'
    },
    {
        src: '/videos/Clip3.mp4',
        poster: '/images/posters/poster3.jpg',
        title: 'Estabilidad',
        description: 'Confianza y seguridad'
    },
    {
        src: '/videos/Clip4.mp4',
        poster: '/images/posters/poster4.jpg',
        title: 'Social',
        description: 'Conexiones que importan'
    },
    {
        src: '/videos/Clip5.mp4',
        poster: '/images/posters/poster5.jpg',
        title: 'Experiencia',
        description: 'Calidad garantizada'
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
    // Reproducir el video actual
    const video = videoRefs.value[currentSlide.value];
    if (video) {
        video.currentTime = 0;
        video.play().catch(err => console.log('Autoplay prevented:', err));
    }
};

const handleVideoEnd = (index) => {
    // Cuando el video termina, avanzar al siguiente slide
    if (index === currentSlide.value && swiperInstance) {
        swiperInstance.slideNext();
    }
};

onMounted(() => {
    // Reproducir todos los videos en loop
    videoRefs.value.forEach((video, index) => {
        if (video) {
            video.addEventListener('ended', () => handleVideoEnd(index));
        }
    });

    setTimeout(() => {
        showIntro.value = false;
    }, 4000); // 4 segundos de intro

    // Delay video play to after transition starts
    setTimeout(() => {
        if (videoRefs.value[0]) {
            videoRefs.value[0].play().catch(err => console.log('Autoplay prevented:', err));
        }
    }, 5000);

    // Add scroll listener for parallax effect
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    videoRefs.value.forEach((video, index) => {
        if (video) {
            video.removeEventListener('ended', () => handleVideoEnd(index));
        }
    });

    // Remove scroll listener
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <Head title="Welcome" />

    <!-- Splash Screen Intro -->
    <div v-if="showIntro" class="fixed inset-0 z-50 flex flex-col items-center justify-center transition-all ease-in-out bg-black duration-2000">
        <div class="flex items-center mb-8 space-x-8 animate-fade-in-stagger">
            <img src="/images/posters/university-logo.png" alt="Universidad" class="w-auto h-32 animate-fade-in-logo">
            <img src="/images/posters/logo-team.png" alt="Equipo" class="w-auto h-32 delay-200 animate-fade-in-logo">
        </div>
        <h1 class="mb-2 text-4xl font-bold text-white animate-pulse animate-fade-in-text delay-400">Bienvenido</h1>
        <p class="text-lg text-white animate-fade-in-text delay-600">A Campus Market</p>
    </div>

    <!-- Transition Overlay -->
    <div
        class="fixed inset-0 z-40 transition-opacity ease-in-out bg-black duration-5000"
        :style="{
            opacity: showIntro ? 1 : 0,
            pointerEvents: showIntro ? 'auto' : 'none'
        }"
    ></div>

    <!-- Sección de Videos Hero -->
    <Transition name="smooth-reveal">
        <div v-show="!showIntro" class="relative h-screen overflow-hidden">
            <Swiper
                :modules="modules"
                :slides-per-view="1"
                :space-between="0"
                :loop="true"
                :effect="'fade'"
                :speed="1500"
                :allowTouchMove="false"
                @swiper="onSwiper"
                @slideChange="onSlideChange"
                class="h-full"
            >
            <SwiperSlide v-for="(video, index) in videos" :key="index">
                <div class="relative h-full">
                    <video
                        :ref="el => videoRefs[index] = el"
                        class="absolute inset-0 object-cover w-full h-full transition-transform duration-100"
                        :style="{ transform: `scale(${1 + scrollY * 0.0002}) translateY(${scrollY * 0.1}px)` }"
                        muted
                        playsinline
                        :poster="video.poster"
                        preload="auto"
                    >
                        <source :src="video.src" type="video/mp4">
                    </video>

                    <div class="absolute inset-0 transition-opacity duration-100 bg-gradient-to-b from-black/60 via-black/40 to-black/70" :style="{ opacity: 1 - scrollY * 0.001 }"></div>

                    <div class="relative z-50 flex flex-col h-full">
                        <header class="flex items-center justify-between p-6 lg:p-10">
                            <div class="flex items-center">
                                <img src="/images/posters/logo-team.png" alt="Team Logo" class="w-auto h-24 text-white lg:h-28">
                            </div>
                            <nav v-if="canLogin" class="flex gap-4">
                                <Link
                                    v-if="$page.props.auth.user"
                                    :href="route('dashboard')"
                                    class="px-4 py-2 text-white transition rounded-md bg-white/10 backdrop-blur-sm hover:bg-white/20"
                                >
                                    Dashboard
                                </Link>
                                <template v-else>
                                    <Link
                                        :href="route('login')"
                                        class="px-4 py-2 text-white transition rounded-md hover:bg-white/10"
                                    >
                                        Iniciar Sesión
                                    </Link>
                                    <Link
                                        v-if="canRegister"
                                        :href="route('register')"
                                        class="rounded-md px-4 py-2 text-white bg-[#FF2D20] hover:bg-[#FF2D20]/90 transition"
                                    >
                                        Registrarse
                                    </Link>
                                </template>
                            </nav>
                        </header>

                        <div class="flex items-center justify-center flex-1 px-6 -mt-20" :style="{ transform: `translateY(${scrollY * 0.3}px)` }">
                            <div class="max-w-4xl text-center text-white transition-transform duration-100">
                                <p class="mb-3 text-lg font-semibold tracking-wider uppercase md:text-xl text-white/80">
                                    Campus Market
                                </p>
                                <h1 class="mb-4 text-5xl font-bold md:text-7xl">
                                    {{ video.title }}
                                </h1>
                                <p class="mb-6 text-xl md:text-2xl text-white/90">
                                    {{ video.description }}
                                </p>
                                <div class="flex flex-wrap justify-center gap-4">
                                    <a href="#content" class="px-8 py-3 bg-[#FF2D20] text-white rounded-lg hover:bg-[#FF2D20]/90 transition font-semibold">
                                        Explorar
                                    </a>
                                    <Link
                                        v-if="!$page.props.auth.user"
                                        :href="route('register')"
                                        class="px-8 py-3 font-semibold text-white transition rounded-lg bg-white/10 backdrop-blur-sm hover:bg-white/20"
                                    >
                                        Comenzar
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center gap-2 pb-10">
                            <button
                                v-for="(_, idx) in videos"
                                :key="idx"
                                @click="swiperInstance?.slideTo(idx)"
                                :class="[
                                    'w-12 h-1 rounded-full transition-all',
                                    currentSlide === idx ? 'bg-white' : 'bg-white/30'
                                ]"
                            ></button>
                        </div>
                    </div>
                </div>
            </SwiperSlide>
            </Swiper>

            <div class="absolute z-50 -translate-x-1/2 bottom-20 left-1/2 animate-bounce" :style="{ transform: `translate(-50%, ${scrollY * 0.5}px)` }">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </div>
        </div>
    </Transition>

    <!-- Contenido Laravel -->
    <div id="content" class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white py-16" :style="{ transform: `translateY(${scrollY * -0.2}px)` }">
            <div class="relative w-full max-w-2xl px-6 transition-transform duration-100 lg:max-w-7xl">
                <main class="mt-6">
                    <div class="mb-12 text-center">
                        <h2 class="text-4xl font-bold text-black mb-14 dark:text-white">Bienvenido a Campus Market</h2>
                        <p class="text-lg text-black/70 dark:text-white/70">Explora las herramientas y recursos disponibles</p>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                        <a
                            href="https://laravel.com/docs"
                            class="flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-6 shadow-lg ring-1 ring-white/[0.05] transition duration-300 hover:shadow-xl hover:ring-black/20 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:ring-zinc-700"
                        >
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10">
                                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path fill="#FF2D20" d="M23 4a1 1 0 0 0-1.447-.894L12.224 7.77a.5.5 0 0 1-.448 0L2.447 3.106A1 1 0 0 0 1 4v13.382a1.99 1.99 0 0 0 1.105 1.79l9.448 4.728c.14.065.293.1.447.1.154-.005.306-.04.447-.105l9.453-4.724a1.99 1.99 0 0 0 1.1-1.789V4Z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-black dark:text-white">Documentación</h3>
                                <p class="mt-2 text-sm text-black/70 dark:text-white/70">
                                    Documentación completa de Laravel para comenzar tu proyecto.
                                </p>
                            </div>
                        </a>

                        <a
                            href="https://laracasts.com"
                            class="flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-6 shadow-lg ring-1 ring-white/[0.05] transition duration-300 hover:shadow-xl hover:ring-black/20 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:ring-zinc-700"
                        >
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10">
                                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><g fill="#FF2D20"><path d="M24 8.25a.5.5 0 0 0-.5-.5H.5a.5.5 0 0 0-.5.5v12a2.5 2.5 0 0 0 2.5 2.5h19a2.5 2.5 0 0 0 2.5-2.5v-12Z"/></g></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-black dark:text-white">Laracasts</h3>
                                <p class="mt-2 text-sm text-black/70 dark:text-white/70">
                                    Miles de tutoriales en video sobre Laravel y desarrollo web.
                                </p>
                            </div>
                        </a>
                    </div>
                </main>

                <footer class="py-16 text-sm text-center text-black/70 dark:text-white/70">
                    Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                </footer>
            </div>
        </div>
    </div>
</template>

<style>
html, body {
    background: #000000;
    margin: 0;
    padding: 0;
}



.smooth-reveal-enter-active, .smooth-reveal-leave-active {
    transition: filter 5s ease-in-out, opacity 5s ease-in-out;
}
.smooth-reveal-enter-from, .smooth-reveal-leave-to {
    opacity: 0;
    filter: brightness(0);
}
.smooth-reveal-enter-to, .smooth-reveal-leave-from {
    opacity: 1;
    filter: brightness(1);
}

@keyframes fade-in-logo {
    from {
        opacity: 0;
        transform: scale(0.8) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes fade-in-text {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in-slow {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.animate-fade-in-logo {
    animation: fade-in-logo 1s ease-out forwards;
}

.animate-fade-in-text {
    animation: fade-in-text 0.8s ease-out forwards;
}

.animate-fade-in-slow {
    animation: fade-in-slow 2s ease-out forwards;
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 1s ease-out;
}

.delay-200 {
    animation-delay: 0.2s;
}

.delay-400 {
    animation-delay: 0.4s;
}

.delay-600 {
    animation-delay: 0.6s;
}

.animate-fade-in-stagger {
    animation: fade-in-stagger 2s ease-out forwards;
}

@keyframes fade-in-stagger {
    0% { opacity: 0; }
    50% { opacity: 1; }
    100% { opacity: 1; }
}
</style>
