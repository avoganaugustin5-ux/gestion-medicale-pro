<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    clinics: Array,
    services: Array, // Ajout des services
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'patient', 
    specialite: '', 
    service_id: '', // Ajouté
    telephone: '',  
    clinic_id: '',  
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
        onError: (errors) => {
            console.error("Erreurs de validation :", errors);
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Inscription - UTS Santé" />

        <form @submit.prevent="submit" class="p-6">
            <h1 class="text-2xl font-black text-slate-800 mb-6 uppercase italic">Créer un compte</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="name" value="Nom & Prénom" />
                    <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="telephone" value="Téléphone" />
                    <TextInput id="telephone" type="text" class="mt-1 block w-full" v-model="form.telephone" required />
                    <InputError class="mt-2" :message="form.errors.telephone" />
                </div>
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email professionnel ou personnel" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="role" value="Votre fonction" />
                <select v-model="form.role" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500">
                    <option value="patient">Patient</option>
                    <option value="medecin">Médecin</option>
                    <option value="secretaire">Secrétaire</option>
                </select>
            </div>

            <div v-if="['medecin', 'secretaire'].includes(form.role)" class="mt-4 p-4 bg-indigo-50 rounded-2xl border border-indigo-100">
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Clinique d'affectation" />
                        <select v-model="form.clinic_id" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm" required>
                            <option value="">Sélectionner une clinique</option>
                            <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">{{ clinic.name }}</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.clinic_id" />
                    </div>

                    <div v-if="form.role === 'medecin'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Spécialité" />
                            <TextInput type="text" class="mt-1 block w-full" v-model="form.specialite" placeholder="Ex: Cardiologue" />
                        </div>
                        <div>
                            <InputLabel value="Service" />
                            <select v-model="form.service_id" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm">
                                <option value="">Sélectionner un service</option>
                                <option v-for="service in services" :key="service.id" :value="service.id">{{ service.nom }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <InputLabel for="password" value="Mot de passe" />
                    <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>
                <div>
                    <InputLabel for="password_confirmation" value="Confirmation" />
                    <TextInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" required />
                </div>
            </div>

            <div class="flex items-center justify-between mt-8">
                <Link :href="route('login')" class="text-sm text-gray-600 hover:text-indigo-600 font-medium">Déjà un compte ?</Link>
                <PrimaryButton class="bg-indigo-600 hover:bg-indigo-700 py-3 px-8 rounded-xl" :disabled="form.processing">
                    Créer mon compte
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>