<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    clinics: Array,
    services: Array
});

const doctors = ref([]);
const loadingDoctors = ref(false);
const today = new Date().toISOString().slice(0, 16); 

const form = useForm({
    clinic_id: '',
    service_id: '',
    doctor_id: '',
    appointment_date: '', // Harmonisé avec le contrôleur
    reason: '',           // Harmonisé avec le contrôleur
});

// Chargement dynamique des médecins via les critères Clinique + Service
watch([() => form.service_id, () => form.clinic_id], async ([newService, newClinic]) => {
    if (newService && newClinic) {
        loadingDoctors.value = true;
        try {
            // Utilisation d'une route de filtrage plus générique et robuste
            const response = await axios.post(route('appointments.getDoctors'), {
                clinic_id: newClinic,
                service_id: newService
            });
            doctors.value = response.data;
        } catch (error) {
            console.error("Erreur lors du chargement des médecins", error);
            doctors.value = [];
        } finally {
            loadingDoctors.value = false;
        }
    } else {
        doctors.value = [];
    }
});

const submit = () => {
    form.post(route('appointments.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Nouveau Rendez-vous" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-black text-2xl text-blue-900 uppercase tracking-tight">Prendre un rendez-vous</h2>
        </template>

        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-2xl rounded-[2.5rem] border border-slate-100 overflow-hidden flex flex-col md:flex-row">
                    
                    <div class="md:w-1/3 bg-blue-600 p-10 text-white flex flex-col justify-center">
                        <h3 class="text-2xl font-black mb-6 uppercase tracking-tight">Besoin d'aide ?</h3>
                        <ul class="space-y-6 text-blue-100">
                            <li class="flex items-center gap-4">
                                <span class="bg-white text-blue-600 rounded-full h-8 w-8 flex items-center justify-center font-black shadow-lg">1</span>
                                <p class="text-sm font-bold">Sélectionnez la clinique et le service.</p>
                            </li>
                            <li class="flex items-center gap-4">
                                <span class="bg-white text-blue-600 rounded-full h-8 w-8 flex items-center justify-center font-black shadow-lg">2</span>
                                <p class="text-sm font-bold">Choisissez votre médecin.</p>
                            </li>
                            <li class="flex items-center gap-4">
                                <span class="bg-white text-blue-600 rounded-full h-8 w-8 flex items-center justify-center font-black shadow-lg">3</span>
                                <p class="text-sm font-bold">Validez la date.</p>
                            </li>
                        </ul>
                    </div>

                    <div class="md:w-2/3 p-10 bg-white">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Clinique</label>
                                    <select v-model="form.clinic_id" class="w-full rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 font-bold text-slate-700 transition-all" required>
                                        <option value="">Sélectionner une clinique</option>
                                        <option v-for="c in clinics" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                    <div v-if="form.errors.clinic_id" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.clinic_id }}</div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Service</label>
                                    <select v-model="form.service_id" class="w-full rounded-xl border-slate-200 focus:ring-blue-500 font-bold text-slate-700 transition-all" required>
                                        <option value="">Sélectionner un service</option>
                                        <option v-for="s in services" :key="s.id" :value="s.id">{{ s.nom }}</option>
                                    </select>
                                    <div v-if="form.errors.service_id" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.service_id }}</div>
                                </div>
                            </div>

                            <div v-if="form.service_id && form.clinic_id" class="pt-2">
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Médecin disponible</label>
                                
                                <div v-if="loadingDoctors" class="flex items-center gap-3 p-4 bg-blue-50 rounded-xl text-blue-600 font-bold text-sm animate-pulse">
                                    <div class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                                    Recherche des praticiens...
                                </div>

                                <div v-else-if="doctors.length > 0">
                                    <select v-model="form.doctor_id" class="w-full rounded-xl border-slate-200 focus:ring-blue-500 font-bold text-slate-700 transition-all" required>
                                        <option value="">Choisir un docteur</option>
                                        <option v-for="d in doctors" :key="d.id" :value="d.id">
                                            Dr. {{ d.user?.name || d.name }}
                                        </option>
                                    </select>
                                </div>

                                <div v-else class="p-4 bg-red-50 text-red-600 text-xs rounded-xl border border-red-100 font-bold flex items-center gap-2">
                                    <span>⚠️</span> Désolé, aucun médecin n'est rattaché à ce service dans cette clinique.
                                </div>
                                <div v-if="form.errors.doctor_id" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.doctor_id }}</div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Date et Heure</label>
                                    <input type="datetime-local" :min="today" v-model="form.appointment_date" class="w-full rounded-xl border-slate-200 focus:ring-blue-500 font-bold text-slate-700 transition-all" required>
                                    <div v-if="form.errors.appointment_date" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.appointment_date }}</div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Motif de consultation</label>
                                    <textarea v-model="form.reason" rows="1" class="w-full rounded-xl border-slate-200 focus:ring-blue-500 font-bold text-slate-700 transition-all" placeholder="Ex: Douleurs thoraciques..."></textarea>
                                    <div v-if="form.errors.reason" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.reason }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-6 pt-6">
                                <Link :href="route('dashboard')" class="text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors tracking-widest">
                                    Annuler
                                </Link>
                                <button type="submit" :disabled="form.processing || !form.doctor_id" 
                                    class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest shadow-xl shadow-blue-200 hover:bg-slate-900 disabled:bg-slate-200 disabled:shadow-none transition-all transform active:scale-95">
                                    {{ form.processing ? 'Traitement...' : 'Confirmer le RDV' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>