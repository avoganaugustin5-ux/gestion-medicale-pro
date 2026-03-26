<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue'; 

const props = defineProps({
    clinic: Object,
    appointments: Array,
    doctors: Array,
    patients: Array,
});

// --- TA LOGIQUE DE RECHERCHE ICI ---
const searchQuery = ref('');

const filteredAppointments = computed(() => {
    return props.appointments.filter(app => {
        const searchTerm = searchQuery.value.toLowerCase();
        const patientName = `${app.patient?.last_name} ${app.patient?.first_name}`.toLowerCase();
        const doctorName = `dr ${app.doctor?.last_name}`.toLowerCase();
        
        return patientName.includes(searchTerm) || doctorName.includes(searchTerm);
    });
});

const form = useForm({
    doctor_id: '',
    patient_id: '',
    appointment_date: '',
    reason: '',
});

const submit = () => {
    form.post(route('clinics.appointments.store', props.clinic.id), {
        onSuccess: () => form.reset(),
    });
};

const validateAppointment = (id) => {
    if (confirm('Confirmer ce rendez-vous ?')) {
        useForm({}).patch(route('appointments.validate', id));
    }
};
</script>

<template>
    <Head :title="'Planning - ' + clinic.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestion des Rendez-vous</h2>
                <Link :href="route('clinics.show', clinic.id)" class="text-sm text-blue-600 underline">← Retour</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div class="bg-white p-6 shadow sm:rounded-lg border-t-4 border-blue-600">
                    <h3 class="text-lg font-bold mb-4">Nouvelle demande de rendez-vous</h3>
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Patient</label>
                            <select v-model="form.patient_id" class="w-full mt-1 rounded-md border-gray-300 shadow-sm" required>
                                <option value="" disabled>Sélectionner...</option>
                                <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.last_name }} {{ p.first_name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Médecin</label>
                            <select v-model="form.doctor_id" class="w-full mt-1 rounded-md border-gray-300 shadow-sm" required>
                                <option value="" disabled>Sélectionner...</option>
                                <option v-for="d in doctors" :key="d.id" :value="d.id">Dr. {{ d.last_name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date et Heure</label>
                            <input v-model="form.appointment_date" type="datetime-local" class="w-full mt-1 rounded-md border-gray-300 shadow-sm" required />
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">Enregistrer</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="p-4 bg-gray-50 border-b flex items-center justify-between">
                        <h3 class="font-bold text-gray-700">Liste des rendez-vous</h3>
                        <div class="relative w-1/3">
                            <input v-model="searchQuery" type="text" placeholder="Rechercher..." class="w-full text-sm rounded-lg border-gray-300" />
                            <span class="absolute right-3 top-2 text-gray-400">🔍</span>
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Médecin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date / Heure</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="app in filteredAppointments" :key="app.id">
                                <td class="px-6 py-4 font-bold uppercase">{{ app.patient?.last_name }} {{ app.patient?.first_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">Dr. {{ app.doctor?.last_name }}</td>
                                <td class="px-6 py-4 text-sm">{{ new Date(app.appointment_date).toLocaleString() }}</td>
                                <td class="px-6 py-4">
                                    <span :class="app.status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-1 rounded-full text-xs font-bold uppercase">
                                        {{ app.status === 'pending' ? 'En attente' : 'Confirmé' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button v-if="app.status === 'pending'" @click="validateAppointment(app.id)" class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700">Confirmer</button>
                                    <span v-else class="text-xs text-gray-400 italic">Validé</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>