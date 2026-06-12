<script setup>
import { ref } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    apellidos: usePage().props.userPerfil?.apellidos || '',
    telefono: usePage().props.userPerfil?.telefono || '',
    Cod_Carrera: usePage().props.userPerfil?.Cod_Carrera || '',
    Cod_Universidad: usePage().props.userPerfil?.Cod_Universidad || '',
    photo: null,
});

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearPhotoFileInput(),
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (! photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
</script>

<template>
    <FormSection @submitted="updateProfileInformation">
        <template #title>
            Información del perfil
        </template>

        <template #description>
            Actualiza la información de perfil y la dirección de correo electrónico de tu cuenta.
        </template>

        <template #form>
            <!-- Profile Photo -->
            <div v-if="$page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input
                    id="photo"
                    ref="photoInput"
                    type="file"
                    class="hidden"
                    @change="updatePhotoPreview"
                >

                <InputLabel for="photo" value="Foto" />

                <!-- Current Profile Photo -->
                <div v-show="! photoPreview" class="mt-2">
                    <img :src="user.profile_photo_url" :alt="user.name" class="rounded-full size-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div v-show="photoPreview" class="mt-2">
                    <span
                        class="block rounded-full size-20 bg-cover bg-no-repeat bg-center"
                        :style="'background-image: url(\'' + photoPreview + '\');'"
                    />
                </div>

                <SecondaryButton class="mt-2 me-2" type="button" @click.prevent="selectNewPhoto">
                    Seleccionar una nueva foto
                </SecondaryButton>

                <SecondaryButton
                    v-if="user.profile_photo_path"
                    type="button"
                    class="mt-2"
                    @click.prevent="deletePhoto"
                >
                    Eliminar foto
                </SecondaryButton>

                <InputError :message="form.errors.photo" class="mt-2" />
            </div>

            <!-- Name -->
            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="name" value="Nombres" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full bg-white/50 dark:bg-black/30 border-white/20 dark:border-white/10"
                    required
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <!-- Apellidos -->
            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="apellidos" value="Apellidos" />
                <TextInput
                    id="apellidos"
                    v-model="form.apellidos"
                    type="text"
                    class="mt-1 block w-full bg-white/50 dark:bg-black/30 border-white/20 dark:border-white/10"
                    autocomplete="family-name"
                />
                <InputError :message="form.errors.apellidos" class="mt-2" />
            </div>

            <!-- Teléfono -->
            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="telefono" value="Teléfono" />
                <TextInput
                    id="telefono"
                    v-model="form.telefono"
                    type="text"
                    class="mt-1 block w-full bg-white/50 dark:bg-black/30 border-white/20 dark:border-white/10"
                    autocomplete="tel"
                />
                <InputError :message="form.errors.telefono" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="email" value="Correo electrónico" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full bg-white/50 dark:bg-black/30 border-white/20 dark:border-white/10"
                    required
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" class="mt-2" />

                <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
                    <p class="text-sm mt-2">
                        Tu dirección de correo no está verificada.

                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click.prevent="sendEmailVerification"
                        >
                            Haz clic aquí para reenviar el correo de verificación.
                        </Link>
                    </p>

                    <div v-show="verificationLinkSent" class="mt-2 font-medium text-sm text-green-600">
                        Se ha enviado un nuevo enlace de verificación a tu correo.
                    </div>
                </div>
            </div>

            <!-- Universidad y Carrera -->
            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="universidad" value="Universidad" />
                <select
                    id="universidad"
                    v-model="form.Cod_Universidad"
                    class="mt-1 block w-full px-4 py-2.5 bg-white/50 dark:bg-black/30 backdrop-blur-md border border-white/20 dark:border-white/10 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-all font-medium text-sm shadow-inner appearance-none"
                >
                    <option value="" disabled>Selecciona tu universidad</option>
                    <option v-for="uni in $page.props.universidadesGlobales" :key="uni.Cod_Universidad" :value="uni.Cod_Universidad">
                        {{ uni.Nombre_Universidad }}
                    </option>
                </select>
                <InputError :message="form.errors.Cod_Universidad" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="carrera" value="Carrera" />
                <select
                    id="carrera"
                    v-model="form.Cod_Carrera"
                    class="mt-1 block w-full px-4 py-2.5 bg-white/50 dark:bg-black/30 backdrop-blur-md border border-white/20 dark:border-white/10 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-all font-medium text-sm shadow-inner appearance-none"
                >
                    <option value="" disabled>Selecciona tu carrera</option>
                    <option v-for="carrera in $page.props.carrerasGlobales" :key="carrera.Cod_Carrera" :value="carrera.Cod_Carrera">
                        {{ carrera.Nombre_Carrera }}
                    </option>
                </select>
                <InputError :message="form.errors.Cod_Carrera" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Guardado.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Guardar
            </PrimaryButton>
        </template>
    </FormSection>
</template>
