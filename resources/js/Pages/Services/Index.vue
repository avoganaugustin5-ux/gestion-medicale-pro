<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    services: Array,
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
</script>

<template>
    <Head title="Gestion des Services" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-black text-2xl text-slate-800 uppercase tracking-tight">Services Médicaux</h2>
        </template>

        <div class="py-12 px-4 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="bg-white p-8 shadow-xl shadow-slate-200/50 rounded-[2rem] border border-slate-100 h-fit">
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

                        <div v-if="unassignedDoctors.length > 0" class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h4 class="text-yellow-800 font-bold">Médecins à affecter :</h4>
                            <div v-for="doc in unassignedDoctors" :key="doc.id" class="flex items-center justify-between mt-2">
                                <span>{{ doc.name }} ({{ doc.specialty }})</span>
                                <select @change="(e) => assignService(doc.id, e.target.value)" class="rounded text-sm">
                                    <option value="">Choisir un service...</option>
                                    <option v-for="service in services" :key="service.id" :value="service.id">
                                        {{ service.nom }}
                                    </option>
                                </select>
                            </div>
                        </div>


                        <button :disabled="form.processing" class="w-full bg-indigo-600 text-white py-4 rounded-xl font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-lg shadow-indigo-200 disabled:opacity-50">
                            {{ form.processing ? 'Création...' : 'Enregistrer le service' }}
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-2 bg-white shadow-xl shadow-slate-200/50 rounded-[2rem] border border-slate-100 overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Nom du service</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Catégorie</th>
                                <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="service in services" :key="service.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5 font-bold text-slate-700">{{ service.nom }}</td>
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase">
                                        {{ service.categorie }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <button class="text-slate-400 hover:text-red-600 font-bold text-xs uppercase tracking-tighter transition-colors">Supprimer</button>
                                </td>
                            </tr>
                            <tr v-if="services.length === 0">
                                <td colspan="3" class="px-8 py-10 text-center text-slate-400 font-medium">Aucun service enregistré.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>