<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    clinic: Object,
    patients: Array,
});

const form = useForm({
    first_name: '',
    last_name: '',
    phone: '',
});

const submit = () => {
    form.post(route('clinics.patients.store', props.clinic.id), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head :title="'Patients - ' + clinic.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Répertoire des Patients : {{ clinic.name }}
                </h2>
                <Link :href="route('clinics.show', clinic.id)" class="text-sm text-blue-600 hover:underline">
                    ← Retour à la gestion
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white p-6 shadow sm:rounded-lg border-b-4 border-green-500">
                    <h3 class="text-lg font-bold mb-4 text-gray-700">Enregistrer un nouveau patient</h3>
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="block text-sm text-gray-600">Nom</label>
                            <input v-model="form.last_name" type="text" class="w-full rounded border-gray-300 shadow-sm" required />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600">Prénom</label>
                            <input v-model="form.first_name" type="text" class="w-full rounded border-gray-300 shadow-sm" required />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600">Téléphone</label>
                            <input v-model="form.phone" type="text" placeholder="Ex: +226..." class="w-full rounded border-gray-300 shadow-sm" required />
                        </div>
                        <button type="submit" :disabled="form.processing" class="bg-green-600 text-white px-4 py-2 rounded font-bold hover:bg-green-700 transition">
                            + Enregistrer le patient
                        </button>
                    </form>
                </div>

                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom Complet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Téléphone</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="patient in patients" :key="patient.id">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 uppercase">
                                    {{ patient.last_name }} {{ patient.first_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    {{ patient.phone }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <button class="text-blue-600 hover:text-blue-900 font-bold">Dossier</button>
                                </td>
                            </tr>
                            <tr v-if="patients.length === 0">
                                <td colspan="3" class="px-6 py-10 text-center text-gray-400 italic">
                                    Aucun patient enregistré dans cette clinique.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>