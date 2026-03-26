<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    appointments: Array
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <div class="space-y-8">
        <section>
            <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <span class="mr-2">📅</span> Mes prochains rendez-vous
            </h4>

            <div v-if="appointments && appointments.length > 0" class="flex overflow-x-auto pb-4 gap-4 snap-x scrollbar-hide">
                <div v-for="app in appointments" :key="app.id" 
                    class="min-w-[300px] bg-white p-5 rounded-xl shadow-sm border border-gray-100 snap-start relative overflow-hidden"
                >
                    <div :class="app.status === 'pending' ? 'bg-orange-400' : 'bg-green-500'" class="absolute top-0 left-0 w-full h-1"></div>
                    
                    <div class="flex justify-between items-start mb-3">
                        <span :class="app.status === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700'" 
                              class="text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wider">
                            {{ app.status === 'pending' ? 'En attente' : 'Confirmé' }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        <p class="text-sm font-black text-gray-900">{{ formatDate(app.date_rdv) }}</p>
                        <div class="flex items-center text-xs text-gray-600">
                            <span class="font-semibold">Docteur :</span>&nbsp;Dr. {{ app.doctor?.last_name || 'Non assigné' }}
                        </div>
                        <div class="flex items-center text-xs text-blue-700 font-medium">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"/></svg>
                            {{ app.clinic?.name }}
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white border border-dashed border-gray-300 rounded-xl p-8 text-center shadow-sm">
                <p class="text-gray-500 text-sm">Aucun rendez-vous à venir.</p>
                <Link :href="route('appointments.create')" class="text-blue-600 text-xs font-bold hover:text-blue-800 mt-3 inline-block bg-blue-50 px-4 py-2 rounded-full transition">Prendre rendez-vous maintenant</Link>
            </div>
        </section>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>