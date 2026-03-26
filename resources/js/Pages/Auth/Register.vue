<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// On récupère les cliniques passées par le contrôleur
const props = defineProps({
    clinics: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'patient', 
    specialite: '', 
    telephone: '',  
    clinic_id: '',  
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            console.log("Requête terminée.");
            form.reset('password', 'password_confirmation');
        },
        onError: (errors) => {
            // C'EST CETTE LIGNE QUI VA NOUS DIRE POURQUOI ÇA COINCE
            console.error("ERREURS SERVEUR :", errors);
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Inscription" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Nom complet" />
                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="role" value="Vous êtes ?" />
                <select v-model="form.role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500">
                    <option value="patient">Un Patient</option>
                    <option value="medecin">Un Médecin</option>
                    <option value="secretaire">Un(e) Secrétaire</option>
                </select>
            </div>

            <div class="mt-4">
                <InputLabel for="telephone" value="Numéro de téléphone" />
                <TextInput id="telephone" type="text" class="mt-1 block w-full" v-model="form.telephone" required />
                <InputError class="mt-2" :message="form.errors.telephone" />
            </div>

            <div v-if="form.role === 'medecin'" class="mt-4">
                <InputLabel for="specialite" value="Spécialité" />
                <TextInput id="specialite" type="text" class="mt-1 block w-full" v-model="form.specialite" />
                <InputError class="mt-2" :message="form.errors.specialite" />
            </div>

            <div v-if="['medecin', 'secretaire'].includes(form.role)" class="mt-4">
                <InputLabel for="clinic_id" value="Clinique" />
                <select v-model="form.clinic_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500">
                    <option value="">-- Choisissez une clinique --</option>
                    <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">
                        {{ clinic.name }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.clinic_id" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Mot de passe" />
                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmer le mot de passe" />
                <TextInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" required />
            </div>

            <div class="flex items-center justify-end mt-6">
                <Link :href="route('login')" class="underline text-sm text-gray-600 hover:text-gray-900">
                    Déjà inscrit ?
                </Link>

                <button 
                    type="submit" 
                    class="ms-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    S'INSCRIRE
                </button>
            </div>
        </form>
    </GuestLayout>
</template>