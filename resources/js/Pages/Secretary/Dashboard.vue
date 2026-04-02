<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    clinic: Object,
    appointments: Array,
});

// CORRECTION : Utilisation de la bonne route et des bons statuts (confirmed/cancelled)
const changeStatus = (id, newStatus) => {
    router.patch(route('clinics.appointments.updateStatus', { 
        clinic: props.clinic.id, 
        appointment: id 
    }), {
        status: newStatus
    }, {
        onSuccess: () => {
            // Optionnel : ajouter une notification de succès ici
        }
    });
};
</script>

<template>
    <Head title="Espace Secrétariat" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Secrétariat : {{ clinic.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl sm:rounded-lg overflow-hidden">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-bold mb-4 text-blue-700">Gestion des Rendez-vous</h3>
                        
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-500">
                                    <th class="px-6 py-3">Date & Heure</th>
                                    <th class="px-6 py-3">Patient</th>
                                    <th class="px-6 py-3">Médecin / Service</th>
                                    <th class="px-6 py-3">Statut</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="app in appointments" :key="app.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ app.date_rdv }}</td>
                                    <td class="px-6 py-4 font-medium">{{ app.patient.nom }} {{ app.patient.prenom }}</td>
                                    <td class="px-6 py-4">
                                        Dr. {{ app.doctor.last_name }} 
                                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded ml-2">
                                            {{ app.doctor.service.nom }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="{
                                            'px-2 py-1 rounded-full text-xs font-bold': true,
                                            'bg-yellow-100 text-yellow-800': app.status === 'pending',
                                            'bg-green-100 text-green-800': app.status === 'confirmed',
                                            'bg-red-100 text-red-800': app.status === 'cancelled'
                                        }">
                                            {{ app.status === 'confirmed' ? 'Confirmé' : (app.status === 'cancelled' ? 'Annulé' : 'En attente') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <button v-if="app.status === 'pending'" 
                                            @click="changeStatus(app.id, 'confirmed')"
                                            class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 font-bold text-xs uppercase transition">
                                            Valider
                                        </button>
                                        <button v-if="app.status === 'pending'" 
                                            @click="changeStatus(app.id, 'cancelled')"
                                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 font-bold text-xs uppercase transition">
                                            Refuser
                                        </button>
                                        <span v-else class="text-gray-400 italic text-xs">Traitée</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>