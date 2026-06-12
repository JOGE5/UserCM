<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import ConfirmsPassword from '@/Components/ConfirmsPassword.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    requiresConfirmation: Boolean,
    isEmbedded: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({
    code: '',
});

const twoFactorEnabled = computed(
    () => ! enabling.value && page.props.auth.user?.two_factor_enabled,
);

watch(twoFactorEnabled, () => {
    if (! twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    router.post(route('two-factor.enable'), {}, {
        preserveScroll: true,
        onSuccess: () => Promise.all([
            showQrCode(),
            showSetupKey(),
            showRecoveryCodes(),
        ]),
        onFinish: () => {
            enabling.value = false;
            confirming.value = props.requiresConfirmation;
        },
    });
};

const showQrCode = () => {
    return axios.get(route('two-factor.qr-code')).then(response => {
        qrCode.value = response.data.svg;
    });
};

const showSetupKey = () => {
    return axios.get(route('two-factor.secret-key')).then(response => {
        setupKey.value = response.data.secretKey;
    });
}

const showRecoveryCodes = () => {
    return axios.get(route('two-factor.recovery-codes')).then(response => {
        recoveryCodes.value = response.data;
    });
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post(route('two-factor.confirm'), {
        errorBag: "confirmTwoFactorAuthentication",
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
        },
    });
};

const regenerateRecoveryCodes = () => {
    axios
        .post(route('two-factor.recovery-codes'))
        .then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
};
</script>

<template>
    <div :class="{'embedded-mode': isEmbedded}">
        <ActionSection>
            <template #title>
                Autenticación en dos pasos
            </template>

            <template #description>
                Añade seguridad adicional a tu cuenta usando la autenticación en dos pasos.
            </template>

            <template #content>
                <h3 v-if="twoFactorEnabled && ! confirming" class="text-lg font-bold text-gray-900 dark:text-white">
                    Has activado la autenticación en dos pasos.
                </h3>

                <h3 v-else-if="twoFactorEnabled && confirming" class="text-lg font-bold text-gray-900 dark:text-white">
                    Finaliza la activación de la autenticación en dos pasos.
                </h3>

                <h3 v-else class="text-lg font-bold text-gray-900 dark:text-white">
                    No has activado la autenticación en dos pasos.
                </h3>

                <div class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-300">
                    <p>
                        Cuando la autenticación en dos pasos está activada, se te pedirá un token seguro y aleatorio durante el acceso. Puedes obtener este token desde la aplicación Google Authenticator de tu teléfono.
                    </p>
                </div>

                <div v-if="twoFactorEnabled">
                    <div v-if="qrCode">
                        <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-300">
                            <p v-if="confirming" class="font-semibold text-indigo-600 dark:text-indigo-400">
                                Para finalizar la activación, escanea el siguiente código QR con la aplicación autenticadora de tu teléfono o ingresa la clave de configuración y proporciona el código OTP generado.
                            </p>

                            <p v-else>
                                La autenticación en dos pasos está activada. Escanea el siguiente código QR con la aplicación autenticadora de tu teléfono o ingresa la clave de configuración.
                            </p>
                        </div>

                        <!-- El QR necesita fondo blanco siempre para poder ser escaneado -->
                        <div class="mt-4 p-4 inline-block bg-white rounded-xl shadow-lg qr-container" v-html="qrCode" />

                        <div v-if="setupKey" class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-300">
                            <p class="font-semibold">
                                Clave de configuración: <span class="font-mono text-indigo-500 bg-indigo-500/10 px-2 py-1 rounded" v-html="setupKey"></span>
                            </p>
                        </div>

                        <div v-if="confirming" class="mt-4">
                            <InputLabel for="code" value="Código de Verificación" class="dark:text-gray-300" />

                            <TextInput
                                id="code"
                                v-model="confirmationForm.code"
                                type="text"
                                name="code"
                                class="block mt-2 w-1/2 dark:bg-black/40 dark:border-white/10 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                                inputmode="numeric"
                                autofocus
                                autocomplete="one-time-code"
                                placeholder="Ej. 123456"
                                @keyup.enter="confirmTwoFactorAuthentication"
                            />

                            <InputError :message="confirmationForm.errors.code" class="mt-2" />
                        </div>
                    </div>

                    <div v-if="recoveryCodes.length > 0 && ! confirming">
                        <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-300">
                            <p class="font-semibold text-rose-500 dark:text-rose-400">
                                Guarda estos códigos de recuperación en un gestor de contraseñas seguro. Puedes usarlos para recuperar el acceso a tu cuenta si pierdes el dispositivo de autenticación en dos pasos.
                            </p>
                        </div>

                        <div class="grid gap-2 max-w-xl mt-4 px-6 py-6 font-mono text-sm bg-gray-100 dark:bg-black/50 border dark:border-white/10 rounded-xl">
                            <div v-for="code in recoveryCodes" :key="code" class="dark:text-gray-300 tracking-widest">
                                {{ code }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <div v-if="! twoFactorEnabled">
                        <ConfirmsPassword :skip="!!$page.props.auth.user?.google_id" @confirmed="enableTwoFactorAuthentication">
                            <PrimaryButton type="button" :class="{ 'opacity-25': enabling }" :disabled="enabling" class="bg-indigo-600 hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400 text-white shadow-lg shadow-indigo-500/30">
                                Activar
                            </PrimaryButton>
                        </ConfirmsPassword>
                    </div>

                    <div v-else class="flex flex-wrap gap-3">
                        <ConfirmsPassword :skip="!!$page.props.auth.user?.google_id" @confirmed="confirmTwoFactorAuthentication">
                            <PrimaryButton
                                v-if="confirming"
                                type="button"
                                :class="{ 'opacity-25': enabling || confirmationForm.processing }"
                                :disabled="enabling || confirmationForm.processing"
                                class="bg-indigo-600 hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400 text-white shadow-lg shadow-indigo-500/30"
                            >
                                Confirmar
                            </PrimaryButton>
                        </ConfirmsPassword>

                        <ConfirmsPassword :skip="!!$page.props.auth.user?.google_id" @confirmed="regenerateRecoveryCodes">
                            <SecondaryButton
                                v-if="recoveryCodes.length > 0 && ! confirming"
                            >
                                Regenerar códigos
                            </SecondaryButton>
                        </ConfirmsPassword>

                        <ConfirmsPassword :skip="!!$page.props.auth.user?.google_id" @confirmed="showRecoveryCodes">
                            <SecondaryButton
                                v-if="recoveryCodes.length === 0 && ! confirming"
                            >
                                Mostrar códigos
                            </SecondaryButton>
                        </ConfirmsPassword>

                        <ConfirmsPassword :skip="!!$page.props.auth.user?.google_id" @confirmed="disableTwoFactorAuthentication">
                            <SecondaryButton
                                v-if="confirming"
                                :class="{ 'opacity-25': disabling }"
                                :disabled="disabling"
                            >
                                Cancelar
                            </SecondaryButton>
                        </ConfirmsPassword>

                        <ConfirmsPassword :skip="!!$page.props.auth.user?.google_id" @confirmed="disableTwoFactorAuthentication">
                            <DangerButton
                                v-if="! confirming"
                                :class="{ 'opacity-25': disabling }"
                                :disabled="disabling"
                            >
                                Desactivar
                            </DangerButton>
                        </ConfirmsPassword>
                    </div>
                </div>
            </template>
        </ActionSection>
    </div>
</template>

<style scoped>
/* Estilos para modo integrado en el Asistente de Perfil */
.embedded-mode :deep(.md\:grid-cols-3) {
    grid-template-columns: 1fr !important;
    gap: 0 !important;
}
.embedded-mode :deep(> div > div:first-child) {
    display: none !important; /* Oculta SectionTitle */
}
.embedded-mode :deep(> div > div:nth-child(2)) {
    grid-column: span 1 / span 1 !important;
    margin-top: 0 !important;
}
/* Hacer transparente solo el contenedor principal de ActionSection */
.embedded-mode :deep(> div > div:nth-child(2) > div) {
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
}

/* Forzar que el QR siempre tenga fondo blanco y trazado negro sin importar el tema global */
.qr-container {
    background-color: white !important;
}
.qr-container :deep(svg) {
    background-color: white !important;
    fill: black !important;
}
.qr-container :deep(svg path) {
    fill: black !important;
}
.qr-container :deep(svg rect) {
    fill: white !important;
}

/* Forzar colores oscuros en los textos y fondos cuando está integrado en el perfil */
.embedded-mode h3, .embedded-mode p {
    color: white !important;
}
.embedded-mode p.text-gray-600 {
    color: #d1d5db !important; /* text-gray-300 */
}
.embedded-mode .grid.bg-gray-100 {
    background-color: rgba(0, 0, 0, 0.4) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}
.embedded-mode .grid.bg-gray-100 div {
    color: #d1d5db !important;
}
</style>
