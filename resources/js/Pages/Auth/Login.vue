<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Connexion - Gestion Médicale" />

    <div class="min-h-screen flex flex-col md:flex-row bg-gray-50">
        <div class="hidden md:flex md:w-1/2 bg-blue-600 items-center justify-center p-12 text-white relative overflow-hidden">
            <div class="relative z-10 space-y-6 max-w-lg">
                <span class="text-5xl block">🏥</span>
                <h1 class="text-4xl font-extrabold tracking-tight">Gestion_RVMC</h1>
                <p class="text-blue-100 text-lg">
                    Plateforme centralisée pour la gestion des cliniques, du personnel médical et du suivi des patients. 
                    Optimisez vos rendez-vous en un clic.
                </p>
                <div class="flex gap-4 pt-4">
                    <div class="bg-blue-500/30 p-4 rounded-lg backdrop-blur-sm border border-blue-400/20">
                        <p class="font-bold text-2xl">100%</p>
                        <p class="text-xs text-blue-100 uppercase tracking-wider">Sécurisé</p>
                    </div>
                    <div class="bg-blue-500/30 p-4 rounded-lg backdrop-blur-sm border border-blue-400/20">
                        <p class="font-bold text-2xl">24/7</p>
                        <p class="text-xs text-blue-100 uppercase tracking-wider">Disponibilité</p>
                    </div>
                </div>
            </div>
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
        </div>

        <div class="flex-1 flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md">
                <div class="mb-10 text-center md:text-left">
                    <h2 class="text-3xl font-bold text-gray-900">Bon retour !</h2>
                    <p class="text-gray-500 mt-2">Veuillez vous connecter pour accéder à votre espace de gestion.</p>
                </div>

                <div v-if="status" class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-lg border border-green-200">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="email" value="Adresse Email" class="text-gray-700 font-semibold" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="augustin@exemple.com"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Mot de passe" class="text-gray-700 font-semibold" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span class="ms-2 text-sm text-gray-600">Se souvenir de moi</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-blue-600 hover:text-blue-500 font-medium"
                        >
                            Mot de passe oublié ?
                        </Link>
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="form.processing"
                        >
                            <svg 
                                v-if="form.processing" 
                                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" 
                                xmlns="http://www.w3.org/2000/svg" 
                                fill="none" 
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>

                            <span v-if="form.processing">Vérification en cours...</span>
                            <span v-else>Se connecter au portail</span>
                        </button>
                    </div>

                    <div class="text-center pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-500">
                            Pas encore de compte ? 
                            <Link :href="route('register')" class="font-medium text-blue-600 hover:text-blue-500">
                                Créer un profil fondateur
                            </Link>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>