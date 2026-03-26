<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    clinics: Array,
    services: Array
});

const doctors = ref([]);
const loadingDoctors = ref(false);
const today = new Date().toISOString().slice(0, 16); // Pour bloquer les dates passées

const form = useForm({
    clinic_id: '',
    service_id: '',
    doctor_id: '',
    date_rdv: '',
    motif: '',
});

// Chargement dynamique des médecins
watch([() => form.service_id, () => form.clinic_id], async ([newService, newClinic]) => {
    if (newService && newClinic) {
        loadingDoctors.value = true;
        try {
            const response = await axios.get(`/api/services/${newService}/clinics/${newClinic}/doctors`);
            doctors.value = response.data;
        } catch (error) {
            console.error("Erreur chargement médecins", error);
        } finally {
            loadingDoctors.value = false;
        }
    } else {
        doctors.value = [];
    }
});

const submit = () => {
    form.post(route('appointments.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Nouveau Rendez-vous" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-blue-900 tracking-tight">Prendre un rendez-vous</h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden flex flex-col md:flex-row">
                    
                    <div class="md:w-1/3 bg-blue-600 p-8 text-white">
                        <h3 class="text-xl font-bold mb-4">Besoin d'aide ?</h3>
                        <ul class="space-y-4 text-blue-100 text-sm">
                            <li class="flex items-start">
                                <span class="bg-blue-500 rounded-full h-5 w-5 flex items-center justify-center mr-2 text-xs">1</span>
                                Sélectionnez la clinique et le service.
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-500 rounded-full h-5 w-5 flex items-center justify-center mr-2 text-xs">2</span>
                                Choisissez votre médecin.
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-500 rounded-full h-5 w-5 flex items-center justify-center mr-2 text-xs">3</span>
                                Validez la date.
                            </li>
                        </ul>
                    </div>

                    <div class="md:w-2/3 p-8">
                        <form @submit.prevent="submit" class="space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black uppercase text-gray-500 mb-1">Clinique</label>
                                    <select v-model="form.clinic_id" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition" required>
                                        <option value="">Sélectionner...</option>
                                        <option v-for="c in clinics" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-black uppercase text-gray-500 mb-1">Service</label>
                                    <select v-model="form.service_id" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 transition" required>
                                        <option value="">Sélectionner...</option>
                                        <option v-for="s in services" :key="s.id" :value="s.id">{{ s.nom }}</option>
                                    </select>
                                </div>
                            </div>

                            <div v-if="form.service_id && form.clinic_id">
                                <label class="block text-xs font-black uppercase text-gray-500 mb-1">Médecin</label>
                                <div v-if="loadingDoctors" class="text-sm text-blue-600 animate-pulse">Recherche des médecins...</div>
                                <select v-else-if="doctors.length > 0" v-model="form.doctor_id" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 transition" required>
                                    <option value="">Choisir un praticien</option>
                                    <option v-for="d in doctors" :key="d.id" :value="d.id">Dr. {{ d.last_name }} - {{ d.specialty }}</option>
                                </select>
                                <div v-else class="p-3 bg-red-50 text-red-600 text-xs rounded-lg border border-red-100">
                                    Désolé, aucun médecin n'est rattaché à ce service dans cette clinique.
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase text-gray-500 mb-1">Date du rendez-vous</label>
                                <input type="datetime-local" :min="today" v-model="form.date_rdv" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 transition" required>
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase text-gray-500 mb-1">Motif (Optionnel)</label>
                                <textarea v-model="form.motif" rows="2" class="w-full rounded-xl border-gray-200 focus:ring-blue-500 transition" placeholder="Ex: Consultation de routine..."></textarea>
                            </div>

                            <div class="flex items-center gap-4 pt-4">
                                <Link :href="route('dashboard')" class="text-sm text-gray-500 font-bold hover:text-gray-700">Annuler</Link>
                                <button type="submit" :disabled="form.processing || !form.doctor_id" 
                                    class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-black shadow-lg shadow-blue-200 hover:bg-blue-700 disabled:bg-gray-300 transition transform active:scale-95">
                                    {{ form.processing ? 'Envoi en cours...' : 'Confirmer le Rendez-vous' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>