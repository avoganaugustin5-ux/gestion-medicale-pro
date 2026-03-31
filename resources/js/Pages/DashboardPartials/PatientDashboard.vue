<script setup>
import { Link } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';

const props = defineProps({
    appointments: Array
});

// Formatage de la date en français (Ex: lun. 30 mars, 14:30)
const formatDate = (dateString) => {
    if (!dateString) return 'Date non définie';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        weekday: 'short', 
        day: 'numeric', 
        month: 'short', 
        hour: '2-digit', 
        minute: '2-digit'
    });
};

// Helper pour les labels et les couleurs des statuts
const getStatusConfig = (status) => {
    switch (status) {
        case 'confirmed':
            return { label: 'Confirmé', class: 'bg-green-100 text-green-700', border: 'bg-green-500' };
        case 'cancelled':
            return { label: 'Annulé', class: 'bg-red-100 text-red-700', border: 'bg-red-500' };
        default:
            return { label: 'En attente', class: 'bg-orange-100 text-orange-700', border: 'bg-orange-400' };
    }
};
</script>

<template>
    <FlashMessage />

    <div class="space-y-8">
        <section>
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-lg font-bold text-gray-800 flex items-center">
                    <span class="mr-2">📅</span> Mes prochains rendez-vous
                </h4>
                <Link v-if="appointments.length > 0" :href="route('appointments.create')" 
                      class="text-[10px] font-black uppercase tracking-widest text-blue-600 bg-blue-50 px-4 py-2 rounded-lg hover:bg-blue-100 transition">
                    + Nouveau RDV
                </Link>
            </div>

            <div v-if="appointments && appointments.length > 0" class="flex overflow-x-auto pb-6 gap-4 snap-x scrollbar-hide">
                <div v-for="app in appointments" :key="app.id" 
                    class="min-w-[320px] bg-white p-6 rounded-2xl shadow-sm border border-gray-100 snap-start relative overflow-hidden hover:shadow-md transition-shadow"
                >
                    <div :class="getStatusConfig(app.status).border" class="absolute top-0 left-0 w-full h-1"></div>
                    
                    <div class="flex justify-between items-start mb-4">
                        <span :class="getStatusConfig(app.status).class" 
                              class="text-[10px] font-black px-2 py-1 rounded-md uppercase tracking-widest">
                            {{ getStatusConfig(app.status).label }}
                        </span>
                        <span class="text-[9px] text-gray-400 font-mono">#RDV-{{ app.id }}</span>
                    </div>

                    <div class="space-y-3">
                        <p class="text-sm font-black text-gray-900 flex items-center">
                            <span class="mr-2">⏰</span> {{ formatDate(app.appointment_date) }}
                        </p>
                        
                        <div class="flex items-center text-xs text-gray-600">
                            <span class="font-bold text-gray-400 uppercase text-[9px] mr-2">Médecin :</span>
                            <span class="font-medium text-gray-800">
                                {{ app.doctor?.user?.name || 'Dr. ' + (app.doctor?.last_name || 'Non assigné') }}
                            </span>
                        </div>

                        <div class="flex items-center text-xs text-indigo-600 font-bold bg-indigo-50/50 p-2 rounded-lg">
                            <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"/>
                            </svg>
                            {{ app.clinic?.name || 'Centre Médical UTS' }}
                        </div>

                        <hr class="border-gray-50">

                        <div v-if="app.reason" class="text-[11px] text-gray-500 italic leading-relaxed">
                            <span class="font-bold not-italic text-gray-400">Votre motif :</span> "{{ app.reason }}"
                        </div>

                        <div v-if="app.status === 'cancelled' && app.cancel_reason" 
                             class="mt-2 p-3 bg-red-50 rounded-xl border border-red-100">
                            <p class="text-[10px] font-black text-red-700 uppercase tracking-tighter mb-1">Note de la secrétaire :</p>
                            <p class="text-[11px] text-red-600 font-medium italic">"{{ app.cancel_reason }}"</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white border-2 border-dashed border-gray-200 rounded-[2rem] p-12 text-center shadow-sm">
                <div class="text-4xl mb-4">🍃</div>
                <p class="text-gray-500 font-medium italic mb-6">Vous n'avez aucun rendez-vous prévu pour le moment.</p>
                <Link :href="route('appointments.create')" class="inline-flex items-center px-8 py-3 bg-blue-600 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all active:scale-95">
                    Prendre mon premier RDV
                </Link>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* Cache la scrollbar pour un look plus "app mobile" */
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>