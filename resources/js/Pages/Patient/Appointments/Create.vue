<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    clinics: Array,
    services: Array,
    hasPendingAppointment: Boolean // La sécurité qu'on a ajoutée au contrôleur
});

const form = useForm({
    clinic_id: '',
    service_id: '',
    appointment_date: '',
    reason: '',
});

const submit = () => {
    form.post(route('appointments.store'));
};
</script>

<template>
    <Head title="Prendre Rendez-vous" />

    <AuthenticatedLayout>
        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-3xl mx-auto px-4">
                
                <div class="mb-8 flex justify-between items-center">
                    <div>
                        <p class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.3em] mb-1 italic">Nouveau dossier</p>
                        <h2 class="font-black text-3xl text-slate-800 uppercase tracking-tight">Prendre Rendez-vous</h2>
                    </div>
                    <Link :href="route('dashboard')" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition">
                        ← Annuler
                    </Link>
                </div>

                <div v-if="hasPendingAppointment" class="mb-8 bg-amber-50 border-2 border-amber-100 p-6 rounded-[2rem] flex items-center gap-4 shadow-sm shadow-amber-100">
                    <span class="text-3xl">⚠️</span>
                    <div>
                        <p class="text-amber-800 text-[11px] font-black uppercase tracking-widest leading-relaxed">
                            Demande en cours de traitement
                        </p>
                        <p class="text-amber-600 text-xs font-medium mt-1">
                            Vous avez déjà une demande en attente. Veuillez patienter avant d'en soumettre une nouvelle.
                        </p>
                    </div>
                </div>

                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <form @submit.prevent="submit" class="space-y-8">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Choisir l'établissement</label>
                                <select v-model="form.clinic_id" class="w-full rounded-2xl border-none bg-slate-50 focus:ring-4 focus:ring-indigo-500/10 font-bold text-sm py-4" :disabled="hasPendingAppointment" required>
                                    <option value="">Sélectionner une clinique</option>
                                    <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">{{ clinic.name }}</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Date & Heure souhaitées</label>
                                <input v-model="form.appointment_date" type="datetime-local" class="w-full rounded-2xl border-none bg-slate-50 focus:ring-4 focus:ring-indigo-500/10 font-bold text-sm py-4" :disabled="hasPendingAppointment" required />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Motif de la visite</label>
                            <textarea v-model="form.reason" rows="4" placeholder="Décrivez brièvement vos symptômes..." class="w-full rounded-2xl border-none bg-slate-50 focus:ring-4 focus:ring-indigo-500/10 font-bold text-xs p-5" :disabled="hasPendingAppointment" required></textarea>
                        </div>

                        <div class="pt-4">
                            <button type="submit" 
                                    :disabled="form.processing || hasPendingAppointment" 
                                    class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black text-[11px] uppercase tracking-[0.3em] hover:bg-indigo-600 transition-all shadow-xl shadow-indigo-500/10 disabled:opacity-30 disabled:grayscale">
                                {{ hasPendingAppointment ? 'Action impossible' : 'Confirmer ma demande' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>