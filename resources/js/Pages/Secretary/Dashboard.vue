<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    clinic: Object,
    appointments: Array,
    stats: Object, // Reçu du SecretaryController mis à jour
});

// Utilisation de la route exacte définie dans web.php
const changeStatus = (id, newStatus) => {
    router.patch(route('clinics.appointments.updateStatus', { 
        clinic: props.clinic.id, 
        appointment: id 
    }), {
        status: newStatus
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Notification possible ici
        }
    });
};
</script>

<template>
    <Head title="Espace Secrétariat" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Secrétariat : {{ clinic.name }}
                </h2>
                <span class="text-sm text-blue-600 font-medium italic">Plateforme AKASUTS • UTS</span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-500 p-6">
                        <div class="text-sm font-medium text-gray-500 uppercase">Aujourd'hui</div>
                        <div class="mt-2 flex items-baseline">
                            <div class="text-3xl font-semibold text-gray-900">{{ stats?.today || 0 }}</div>
                            <div class="ml-2 text-xs text-gray-500">Rendez-vous</div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-green-500 p-6">
                        <div class="text-sm font-medium text-gray-500 uppercase">Patients Suivis</div>
                        <div class="mt-2 flex items-baseline">
                            <div class="text-3xl font-semibold text-gray-900">{{ stats?.patients || 0 }}</div>
                            <div class="ml-2 text-xs text-gray-500">Actifs</div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-orange-500 p-6">
                        <div class="text-sm font-medium text-gray-500 uppercase">Journal de bord</div>
                        <div class="mt-2 flex items-baseline">
                            <div class="text-3xl font-semibold text-gray-900">{{ stats?.interactions || 0 }}</div>
                            <div class="ml-2 text-xs text-gray-500">Interactions</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-xl sm:rounded-lg overflow-hidden">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center">
                                <span class="mr-2">📋</span> Demandes de rendez-vous en attente
                            </h3>
                            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                {{ appointments.length }} TOTAL
                            </span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-500 tracking-wider">
                                        <th class="px-6 py-3">Date & Heure</th>
                                        <th class="px-6 py-3">Patient</th>
                                        <th class="px-6 py-3">Médecin / Service</th>
                                        <th class="px-6 py-3">Statut</th>
                                        <th class="px-6 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="app in appointments" :key="app.id" class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ app.appointment_date }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">
                                                {{ app.patient?.user?.name || 'Patient inconnu' }}
                                            </div>
                                            <div class="text-xs text-gray-500">Inscrit via plateforme</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">Dr. {{ app.doctor?.user?.name }}</div>
                                            <div class="text-xs text-blue-600 font-medium">
                                                {{ app.service?.nom }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="{
                                                'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tighter': true,
                                                'bg-yellow-100 text-yellow-800': app.status === 'pending',
                                                'bg-green-100 text-green-800': app.status === 'confirmed',
                                                'bg-red-100 text-red-800': app.status === 'cancelled'
                                            }">
                                                {{ app.status === 'confirmed' ? 'Confirmé' : (app.status === 'cancelled' ? 'Annulé' : 'En attente') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <template v-if="app.status === 'pending'">
                                                <button 
                                                    @click="changeStatus(app.id, 'confirmed')"
                                                    class="inline-flex items-center p-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition"
                                                    title="Valider"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                <button 
                                                    @click="changeStatus(app.id, 'cancelled')"
                                                    class="inline-flex items-center p-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition"
                                                    title="Refuser"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </template>
                                            <span v-else class="text-gray-400 italic text-xs">Dossier classé</span>
                                        </td>
                                    </tr>
                                    <tr v-if="appointments.length === 0">
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                                            Aucun rendez-vous ne correspond à vos médecins rattachés.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>