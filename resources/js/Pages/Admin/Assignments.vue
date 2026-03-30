<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    staff: Array,
    clinics: Array
});

const form = useForm({
    user_id: '',
    clinic_id: '',
});

const submit = () => {
    form.post(route('admin.assignments.store'), {
        onSuccess: () => form.reset(),
    });
};

const detach = (userId) => {
    if (confirm("Voulez-vous vraiment détacher cet agent de sa clinique actuelle ?")) {
        router.post(route('admin.assignments.detach', userId));
    }
};
</script>

<template>
    <Head title="Affectations" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-black text-2xl text-slate-800 uppercase tracking-tight">Personnel & Affectations</h2>
                <Link :href="route('dashboard')" class="text-sm font-bold text-indigo-600 hover:underline">Retour Dashboard</Link>
            </div>
        </template>

        <div class="py-12 bg-slate-50/50 min-h-screen">
            <div class="max-w-5xl mx-auto px-4 space-y-8">
                
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                    <div class="p-8 bg-slate-900 text-white flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold italic">Nouvelle Liaison</h3>
                            <p class="text-slate-400 text-sm">Affecter un agent à une clinique partenaire.</p>
                        </div>
                        <span class="text-3xl">🔗</span>
                    </div>

                    <form @submit.prevent="submit" class="p-8 flex flex-col md:flex-row gap-6 items-end">
                        <div class="flex-1 space-y-2 w-full">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Agent médical</label>
                            <select v-model="form.user_id" class="w-full px-5 py-4 rounded-2xl border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all font-medium">
                                <option value="" disabled>Choisir un agent...</option>
                                <option v-for="member in staff" :key="member.id" :value="member.id">
                                    {{ member.name }} ({{ member.role }})
                                </option>
                            </select>
                        </div>

                        <div class="flex-1 space-y-2 w-full">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Destination</label>
                            <select v-model="form.clinic_id" class="w-full px-5 py-4 rounded-2xl border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all font-medium">
                                <option value="" disabled>Choisir une clinique...</option>
                                <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">
                                    {{ clinic.name }}
                                </option>
                            </select>
                        </div>

                        <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 disabled:opacity-50">
                            Lier l'agent
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/50">
                        <h4 class="font-bold text-slate-800">État actuel du personnel</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/30">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nom de l'Agent</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Rôle</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Clinique Actuelle</th>
                                    <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="member in staff" :key="member.id" class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-700">{{ member.name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ member.email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase" 
                                              :class="member.role === 'medecin' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'">
                                            {{ member.role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="member.clinic" class="flex items-center text-emerald-600 font-bold text-sm">
                                            <span class="mr-2">📍</span> {{ member.clinic.name }}
                                        </div>
                                        <div v-else class="text-slate-300 text-sm italic">En attente d'affectation</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button 
                                            v-if="member.clinic" 
                                            @click="detach(member.id)"
                                            class="text-red-400 hover:text-red-600 text-[10px] font-black uppercase tracking-tighter opacity-0 group-hover:opacity-100 transition-opacity"
                                        >
                                            Déconnecter l'agent
                                        </button>
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