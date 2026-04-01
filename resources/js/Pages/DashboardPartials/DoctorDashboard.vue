<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({ appointments: Array });

// --- ÉTAT DE LA MODALE ---
const confirmingAppointmentCompletion = ref(false);
const selectedAppointment = ref(null);

const formatDate = (dateString) => {
    if (!dateString) return '--:--';
    const date = new Date(dateString);
    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
};

// --- LOGIQUE DE VALIDATION ---
const form = useForm({
    status: 'completed'
});

const openCompletionModal = (appointment) => {
    selectedAppointment.value = appointment;
    confirmingAppointmentCompletion.value = true;
};

const closeModal = () => {
    confirmingAppointmentCompletion.value = false;
    selectedAppointment.value = null;
};

const completeAppointment = () => {
    form.patch(route('clinics.appointments.updateStatus', { 
        clinic: selectedAppointment.value.clinic_id, // Assure-toi que l'objet appointment contient clinic_id
        appointment: selectedAppointment.value.id 
    }), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};
</script>

<template>
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-8 bg-slate-900 text-white flex justify-between items-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-2xl shadow-inner">🩺</div>
                <div>
                    <h4 class="font-black text-xs uppercase tracking-[0.2em] text-indigo-400">Flux de travail</h4>
                    <h3 class="text-xl font-black uppercase tracking-tighter">Consultations du jour</h3>
                </div>
            </div>
            <div class="bg-indigo-600/20 border border-indigo-500/30 px-5 py-2 rounded-2xl flex items-center gap-3">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-[11px] font-black uppercase tracking-widest">{{ appointments.length }} en attente</span>
            </div>
        </div>

        <div class="p-8">
            <div v-if="appointments.length > 0" class="space-y-4">
                <div v-for="app in appointments" :key="app.id" 
                    class="group flex flex-col md:flex-row items-center justify-between p-6 rounded-[2rem] border border-slate-100 bg-slate-50/40 hover:bg-white hover:shadow-2xl hover:shadow-indigo-100/50 transition-all duration-500">
                    
                    <div class="flex items-center space-x-6">
                        <div class="flex flex-col items-center justify-center min-w-[75px] h-[75px] bg-white rounded-[1.5rem] border border-slate-200 shadow-sm group-hover:border-indigo-200 transition-colors">
                            <span class="text-sm font-black text-indigo-600 italic">{{ formatDate(app.appointment_date) }}</span>
                            <span class="text-[9px] text-slate-400 font-black uppercase">Prévu</span>
                        </div>
                        
                        <div>
                            <p class="font-black text-slate-900 text-xl tracking-tight mb-1 uppercase">
                                {{ app.patient?.user?.name || 'Patient Anonyme' }}
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span class="text-[10px] font-black uppercase px-3 py-1 rounded-full shadow-sm" 
                                    :class="app.status === 'completed' ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-white'">
                                    {{ app.status === 'completed' ? 'Soin terminé' : 'En attente' }}
                                </span>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1 bg-white px-3 py-1 rounded-full border border-slate-100">
                                    🏥 {{ app.service?.nom || 'Consultation Générale' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6 md:mt-0">
                        <button v-if="app.status !== 'completed'" 
                            @click="openCompletionModal(app)"
                            class="px-6 py-3 bg-indigo-600 text-white text-[10px] font-black rounded-2xl hover:bg-indigo-700 transition-all uppercase shadow-lg shadow-indigo-200 active:scale-95">
                            ✓ Terminer le soin
                        </button>
                        <button class="px-6 py-3 bg-white border border-slate-200 text-slate-700 text-[10px] font-black rounded-2xl hover:bg-slate-50 transition-all uppercase flex items-center gap-2">
                            <span>📁</span> Dossier
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-24 bg-slate-50/50 rounded-[3rem] border-2 border-dashed border-slate-200">
                <div class="text-6xl mb-6 grayscale opacity-50">📋</div>
                <p class="text-slate-500 italic text-lg font-black uppercase tracking-tighter">Planning dégagé</p>
                <p class="text-slate-400 text-xs font-bold">Tous les patients du système AKASUTS ont été pris en charge.</p>
            </div>
        </div>

        <Modal :show="confirmingAppointmentCompletion" @close="closeModal">
            <div class="p-8">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-3xl mb-6 mx-auto">
                    ⚕️
                </div>
                <h2 class="text-2xl font-black text-slate-900 text-center uppercase tracking-tighter">
                    Valider la fin du soin ?
                </h2>
                <p class="mt-4 text-slate-500 text-center font-medium leading-relaxed">
                    Voulez-vous confirmer que la consultation pour <br>
                    <span class="text-indigo-600 font-black uppercase underline decoration-2 decoration-indigo-200">
                        {{ selectedAppointment?.patient?.user?.name }}
                    </span> est terminée ? <br>
                    <span class="text-[10px] uppercase font-bold text-slate-400">Cette action enregistre l'heure de fin dans le système UTS.</span>
                </p>

                <div class="mt-10 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal" class="!rounded-2xl"> Annuler </SecondaryButton>
                    <button 
                        @click="completeAppointment"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        class="inline-flex items-center px-6 py-2 bg-emerald-600 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:bg-emerald-500 active:bg-emerald-700 transition ease-in-out duration-150"
                    >
                        Confirmer le soin
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>