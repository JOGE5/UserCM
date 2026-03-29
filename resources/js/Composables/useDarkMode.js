import { ref, watch, onMounted } from 'vue';

export function useDarkMode() {
    const isDark = ref(localStorage.getItem('theme') === 'dark' || 
                      (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches));

    const toggleDark = () => {
        isDark.value = !isDark.value;
    };

    watch(isDark, (val) => {
        if (val) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    }, { immediate: true });

    onMounted(() => {
        // Asegurarse de que el tema esté sincronizado al montar
        if (isDark.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    });

    return {
        isDark,
        toggleDark
    };
}
