<script setup>
import { Link } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';

const props = defineProps({
    appointments: Array,
    patient: Object // Ajouté pour recevoir les données du controller
});

const formatDate = (dateString) => {
    if (!dateString) return 'Date non définie';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
    });
};

const getStatusConfig = (status) => {
    const configs = {
        'confirmed': { label: 'Confirmé', class: 'bg-green-100 text-green-700', border: 'bg-green-500', icon: '✅' },
        'cancelled': { label: 'Refusé', class: 'bg-red-100 text-red-700', border: 'bg-red-500', icon: '❌' },
        'pending': { label: 'En attente', class: 'bg-orange-100 text-orange-700', border: 'bg-orange-400', icon: '⏳' },
        'completed': { label: 'Terminé', class: 'bg-blue-100 text-blue-700', border: 'bg-blue-500', icon: '🏁' }
    };
    return configs[status] || configs['pending'];
};
</script>

<template>
    <FlashMessage />

    <div class="space-y-8">
        <section v-if="appointments.some(a => a.status !== 'pending')" class="mb-6">
            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Notifications récentes</h4>
            <div class="grid gap-3">
                <div v-for="app in appointments.filter(a => a.status !== 'pending').slice(0, 3)" :key="'notif-'+app.id" 
                     class="flex items-center p-4 bg-white rounded-2xl border-l-4 shadow-sm"
                     :class="app.status === 'confirmed' ? 'border-green-500' : 'border-red-500'">
                    <span class="text-2xl mr-4">{{ getStatusConfig(app.status).icon }}</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-slate-800">
                            Votre demande pour le {{ formatDate(app.appointment_date) }} a été 
                            {{ app.status === 'confirmed' ? 'acceptée' : 'refusée' }}.
                        </p>
                        <p v-if="app.cancel_reason" class="text-xs text-red-500 italic mt-1">Motif : {{ app.cancel_reason }}</p>
                    </div>
                    <a v-if="app.status === 'confirmed'" :href="route('appointments.downloadTicket', app.id)" 
                       class="ml-4 bg-slate-900 text-white text-[10px] font-black px-3 py-2 rounded-lg uppercase">
                        Ticket PDF
                    </a>
                </div>
            </div>
        </section>

        <section>
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-lg font-bold text-gray-800 flex items-center">
                    <span class="mr-2">📅</span> Mes rendez-vous
                </h4>
                <Link :href="route('appointments.create', { clinic: patient?.clinic_id || 1 })" 
                      class="text-[10px] font-black uppercase tracking-widest text-white bg-indigo-600 px-4 py-2 rounded-lg shadow-lg">
                    + Nouveau RDV
                </Link>
            </div>

            <div v-if="appointments.length > 0" class="flex overflow-x-auto pb-6 gap-4 snap-x scrollbar-hide">
                <div v-for="app in appointments" :key="app.id" 
                    class="min-w-[320px] bg-white p-6 rounded-2xl shadow-sm border border-gray-100 snap-start relative overflow-hidden">
                    <div :class="getStatusConfig(app.status).border" class="absolute top-0 left-0 w-full h-1"></div>
                    
                    <div class="flex justify-between items-start mb-4">
                        <span :class="getStatusConfig(app.status).class" class="text-[10px] font-black px-2 py-1 rounded-md uppercase tracking-widest">
                            {{ getStatusConfig(app.status).label }}
                        </span>
                        <span class="text-[9px] text-gray-400 font-mono">#RDV-{{ app.id }}</span>
                    </div>

                    <div class="space-y-3">
                        <p class="text-sm font-black text-gray-900 flex items-center">
                            <span class="mr-2">⏰</span> {{ formatDate(app.appointment_date) }}
                        </p>
                        <div class="text-xs text-gray-600">
                            <p><span class="font-bold text-gray-400 uppercase text-[9px]">Médecin :</span> Dr. {{ app.doctor?.user?.name || 'En attente' }}</p>
                            <p class="mt-1 font-bold text-indigo-600">{{ app.clinic?.name }}</p>
                        </div>

                        <div v-if="app.status === 'confirmed'" class="pt-4 border-t border-gray-50 flex gap-2">
                             <a :href="route('appointments.downloadTicket', app.id)" class="flex-1 text-center py-2 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-lg uppercase border border-emerald-100">
                                Descendre le Ticket
                             </a>
                        </div>
                        <div v-else-if="app.status === 'cancelled'" class="pt-4 border-t border-gray-50">
                             <Link :href="route('clinics.appointments.create', { clinic: patient?.clinic_id || 1 })" class="block text-center py-2 bg-red-50 text-red-700 text-[10px] font-black rounded-lg uppercase">
                                Refaire une demande
                             </Link>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="bg-white p-10 rounded-[2rem] text-center border-2 border-dashed border-slate-200">
                <p class="text-slate-400 font-bold italic text-sm">Vous n'avez aucun rendez-vous pour le moment.</p>
            </div>
        </section>

        <section class="bg-indigo-900 rounded-[2.5rem] p-8 text-white shadow-xl relative overflow-hidden">
            <div class="relative z-10">
                <h4 class="text-2xl font-black uppercase tracking-tighter mb-2">Mon Carnet de Santé Digitale</h4>
                <p class="text-indigo-200 text-sm mb-6 max-w-md">Accédez à l'historique complet de vos consultations, diagnostics et ordonnances certifiés par l'UTS.</p>
                <div class="flex gap-4">
                    <Link v-if="patient" :href="route('patients.show', patient.id)" 
                          class="bg-white text-indigo-900 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-50 transition">
                        Consulter mes soins
                    </Link>
                    <a v-if="patient" :href="route('patient.medical-record', { patient: patient.id })" 
                       class="bg-indigo-700 text-white px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-800 transition">
                        Télécharger le carnet (PDF)
                    </a>
                </div>
            </div>
            <div class="absolute right-[-20px] top-[-20px] text-[150px] opacity-10 rotate-12">📄</div>
        </section>
    </div>
</template>