<script setup>
import { computed } from 'vue';

const props = defineProps({
    appointments: Array // Reçoit les RDV confirmés du médecin
});

const formatDate = (dateString) => {
    if (!dateString) return '--:--';
    const date = new Date(dateString);
    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/10 rounded-lg backdrop-blur-sm text-xl">🩺</div>
                <div>
                    <h4 class="font-black text-sm uppercase tracking-widest">Planning des Consultations</h4>
                    <p class="text-[10px] text-slate-400 font-medium">Liste des patients confirmés pour aujourd'hui.</p>
                </div>
            </div>
            <div class="bg-indigo-600 px-4 py-1.5 rounded-full border border-indigo-400/30 flex items-center gap-2">
                <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                <span class="text-[10px] font-black uppercase">{{ appointments.length }} Patient(s)</span>
            </div>
        </div>

        <div class="p-6">
            <div v-if="appointments.length > 0" class="grid grid-cols-1 gap-4">
                <div v-for="app in appointments" :key="app.id" 
                    class="group flex flex-col md:flex-row items-start md:items-center justify-between p-5 rounded-2xl border border-slate-100 bg-slate-50/30 hover:bg-white hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300">
                    
                    <div class="flex items-center space-x-5 mb-4 md:mb-0">
                        <div class="flex flex-col items-center justify-center min-w-[60px] h-[60px] bg-white rounded-xl border border-slate-200 shadow-sm group-hover:border-indigo-200 group-hover:bg-indigo-50 transition-colors">
                            <span class="text-xs font-black text-indigo-600">{{ formatDate(app.appointment_date) }}</span>
                            <span class="text-[8px] text-slate-400 font-bold uppercase tracking-tighter">Heure</span>
                        </div>
                        
                        <div>
                            <p class="font-black text-slate-900 text-base flex items-center gap-2">
                                {{ app.patient?.user?.name }}
                                <span v-if="app.reason" class="text-[10px] font-medium bg-amber-100 text-amber-700 px-2 py-0.5 rounded-md">Motif : {{ app.reason }}</span>
                            </p>
                            <p class="text-[10px] text-indigo-600 font-black uppercase tracking-widest mt-1">
                                🏥 {{ app.service?.nom || 'Consultation Générale' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-2 w-full md:w-auto">
                        <button class="flex-1 md:flex-none px-5 py-2.5 bg-white border border-slate-200 text-slate-700 text-[10px] font-black rounded-xl hover:bg-slate-50 transition uppercase tracking-widest active:scale-95">
                            Dossier Patient
                        </button>
                        <button class="flex-1 md:flex-none px-5 py-2.5 bg-indigo-600 text-white text-[10px] font-black rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition uppercase tracking-widest active:scale-95">
                            Valider Soin
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-16">
                <div class="text-4xl mb-4">🍃</div>
                <p class="text-slate-400 italic text-sm font-bold">Votre agenda est vide pour le moment.</p>
                <p class="text-[10px] text-slate-300 uppercase mt-2">Aucune consultation confirmée n'a été transmise par le secrétariat.</p>
            </div>
        </div>
    </div>
</template>