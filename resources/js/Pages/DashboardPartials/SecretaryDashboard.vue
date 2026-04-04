<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    appointments: Array,
    clinic: Object
});

const form = useForm({
    status: '',
    cancel_reason: ''
});

// Sécurité pour récupérer l'ID de la clinique
const clinicId = computed(() => {
    return props.clinic?.id || props.appointments[0]?.clinic_id;
});

const changeStatus = (id, newStatus) => {
    let reason = null;
    if (newStatus === 'cancelled') {
        reason = prompt("Motif du refus (optionnel) :");
    }

    if (confirm(`Confirmer cette action ?`)) {
        form.status = newStatus;
        form.cancel_reason = reason;
        
        form.patch(route('clinics.appointments.updateStatus', { 
            clinic: clinicId.value, 
            appointment: id 
        }), {
            preserveScroll: true,
            onSuccess: () => {
                // Optionnel : un petit feedback visuel si besoin
            }
        });
    }
};
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h4 class="font-bold text-gray-800 flex items-center">
                <span class="mr-2">📥</span> Demandes de rendez-vous en attente
            </h4>
            <span class="bg-blue-600 text-white text-[10px] px-2 py-1 rounded-full font-bold">
                {{ appointments.length }} NOUVEAU(X)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[11px] uppercase text-gray-400 font-black border-b border-gray-100">
                        <th class="p-4">Patient</th>
                        <th class="p-4">Médecin / Service</th>
                        <th class="p-4">Date & Heure</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="app in appointments" :key="app.id" class="hover:bg-blue-50/30 transition">
                        <td class="p-4">
                            <p class="font-bold text-gray-900 text-sm">{{ app.patient?.user?.name || 'Inconnu' }}</p>
                            <p class="text-[10px] text-gray-500">Inscrit via plateforme</p>
                        </td>
                        <td class="p-4">
                            <p class="text-sm font-medium text-gray-700">Dr. {{ app.doctor?.user?.name }}</p>
                            <p class="text-[10px] text-blue-600 font-bold italic">{{ app.service?.nom }}</p>
                        </td>
                        <td class="p-4">
                            <p class="text-sm text-gray-900 font-mono">{{ app.appointment_date }}</p>
                        </td>
                        <td class="p-4 text-right space-x-2">
                            <button @click="changeStatus(app.id, 'confirmed')" 
                                class="inline-flex items-center p-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                            <button @click="changeStatus(app.id, 'cancelled')" 
                                class="inline-flex items-center p-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="appointments.length === 0">
                        <td colspan="4" class="p-8 text-center text-gray-400 italic text-sm">
                            Aucune demande en attente. Travail terminé ! 🎉
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>