<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    services: {
        type: Array,
        default: () => []
    },
    unassignedDoctors: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    nom: '',
    categorie: '',
});

const submit = () => {
    form.post(route('services.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

// Fonction pour assigner un médecin à un service
const assignService = (doctorId, serviceId) => {
    if (!serviceId) return;

    router.post(route('services.attachDoctor'), {
        doctor_id: doctorId,
        service_id: serviceId
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Optionnel : ajouter une notification de succès ici
        }
    });
};
</script>

<template>
    <Head title="Gestion des Services" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-black text-2xl text-slate-800 uppercase tracking-tight">Services Médicaux</h2>
        </template>

        <div class="py-12 px-4 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="space-y-8 h-fit">
                    
                    <div class="bg-white p-8 shadow-xl shadow-slate-200/50 rounded-[2rem] border border-slate-100">
                        <h3 class="text-lg font-black text-slate-800 mb-6 uppercase tracking-wider">Nouveau Service</h3>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 mb-2">Nom du Service</label>
                                <input v-model="form.nom" type="text" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition-all" placeholder="ex: Cardiologie">
                                <div v-if="form.errors.nom" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.nom }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 mb-2">Catégorie</label>
                                <select v-model="form.categorie" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 transition-all">
                                    <option value="" disabled>Choisir...</option>
                                    <option value="A">Spécialité (A)</option>
                                    <option value="B">Général (B)</option>
                                    <option value="C">Urgence (C)</option>
                                </select>
                                <div v-if="form.errors.categorie" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.categorie }}</div>
                            </div>

                            <button :disabled="form.processing" class="w-full bg-indigo-600 text-white py-4 rounded-xl font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-lg shadow-indigo-200 disabled:opacity-50">
                                {{ form.processing ? 'Création...' : 'Enregistrer le service' }}
                            </button>
                        </form>
                    </div>

                    <div v-if="unassignedDoctors && unassignedDoctors.length > 0" class="bg-amber-50 p-6 rounded-[2rem] border border-amber-100 shadow-sm">
                        <h4 class="text-amber-900 font-black text-xs uppercase tracking-widest mb-4 flex items-center">
                            <span class="mr-2">⚠️</span> Médecins à affecter
                        </h4>
                        <div class="space-y-4">
                            <div v-for="doc in unassignedDoctors" :key="doc.id" class="bg-white/60 p-4 rounded-2xl border border-amber-200/50">
                                <p class="text-sm font-bold text-slate-800 mb-2">{{ doc.name }}</p>
                                <select @change="(e) => assignService(doc.id, e.target.value)" class="w-full rounded-lg border-amber-200 text-xs font-bold text-amber-800 bg-white focus:ring-amber-500">
                                    <option value="">Choisir un service cible...</option>
                                    <option v-for="service in services" :key="service.id" :value="service.id">
                                        {{ service.nom }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white shadow-xl shadow-slate-200/50 rounded-[2rem] border border-slate-100 overflow-hidden">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Service & Staff</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Catégorie</th>
                                    <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="service in services" :key="service.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="font-black text-slate-800 uppercase text-sm mb-1">{{ service.nom }}</div>
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            <span v-for="doc in service.doctors" :key="doc.id" class="text-[10px] bg-slate-100 text-slate-600 px-2 py-1 rounded-md font-medium">
                                                Dr. {{ doc.name }}
                                            </span>
                                            <span v-if="!service.doctors || service.doctors.length === 0" class="text-[10px] text-slate-400 italic">
                                                Aucun médecin rattaché
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase">
                                            Zone {{ service.categorie }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <button class="text-slate-300 hover:text-red-600 font-bold text-[10px] uppercase tracking-tighter transition-all">
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="services.length === 0">
                                    <td colspan="3" class="px-8 py-12 text-center">
                                        <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">Aucun service enregistré dans le système.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>