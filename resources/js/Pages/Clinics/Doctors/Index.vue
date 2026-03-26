<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    clinic: Object,
    doctors: Array,
    services: Array,
});

const form = useForm({
    first_name: '',
    last_name: '',
    specialty: '',
    service_id: '',
});

const submit = () => {
    form.post(route('clinics.doctors.store', props.clinic.id), {
        onSuccess: () => form.reset(),
    });
};

// --- NOUVELLE FONCTION AJOUTÉE ICI ---
const deleteDoctor = (id) => {
    if (confirm('Voulez-vous vraiment retirer ce médecin ?')) {
        // On passe l'ID de la clinique ET l'ID du médecin à la route
        form.delete(route('clinics.doctors.destroy', [props.clinic.id, id]), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="'Médecins - ' + clinic.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Personnel Médical : {{ clinic.name }}
                </h2>
                <Link :href="route('clinics.show', clinic.id)" class="text-sm text-blue-600 hover:underline">
                    ← Retour à la gestion
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white p-6 shadow sm:rounded-lg border-b-4 border-blue-500">
                    <h3 class="text-lg font-bold mb-4 text-gray-700">Ajouter un nouveau médecin</h3>
                    
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                        <div>
                            <label class="block text-sm text-gray-600">Nom</label>
                            <input v-model="form.last_name" type="text" class="w-full rounded border-gray-300" required />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600">Prénom</label>
                            <input v-model="form.first_name" type="text" class="w-full rounded border-gray-300" required />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600">Spécialité</label>
                            <input v-model="form.specialty" type="text" placeholder="Ex: Cardiologue" class="w-full rounded border-gray-300" required />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Service Affecté</label>
                            <select v-model="form.service_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Choisir un service...</option>
                                <option v-for="service in services" :key="service.id" :value="service.id">
                                    {{ service.nom }} ({{ service.categorie }})
                                </option>
                            </select>
                        </div>

                        <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-4 py-2 rounded font-bold hover:bg-blue-700 h-[42px]">
                            + Ajouter
                        </button>
                    </form>
                </div>

                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom & Prénom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service / Spécialité</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="doctor in doctors" :key="doctor.id">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    Dr. {{ doctor.last_name }} {{ doctor.first_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    <span class="font-bold text-blue-600" v-if="doctor.service">
                                        [{{ doctor.service.nom }}]
                                    </span>
                                    {{ doctor.specialty }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <button 
                                        @click="deleteDoctor(doctor.id)" 
                                        class="text-red-600 hover:text-red-900 font-bold"
                                    >
                                        Retirer
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="doctors.length === 0">
                                <td colspan="3" class="px-6 py-10 text-center text-gray-400 italic">
                                    Aucun médecin enregistré pour le moment.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>