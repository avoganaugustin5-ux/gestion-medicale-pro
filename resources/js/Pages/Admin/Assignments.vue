<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    staff: Array,
    clinics: Array,
    doctors: Array,
    secretaries: Array,
    currentAssignments: Array
});

// Formulaire 1 : Agent <-> Clinique
const clinicForm = useForm({
    user_id: '',
    clinic_id: '',
});

// Formulaire 2 : Secrétaire <-> Médecin
const secretaryForm = useForm({
    doctor_id: '',
    secretary_id: '',
});

const submitClinic = () => {
    clinicForm.post(route('admin.assignments.clinic.store'), {
        onSuccess: () => clinicForm.reset(),
    });
};

const submitSecretary = () => {
    secretaryForm.post(route('admin.assignments.secretary.store'), {
        onSuccess: () => secretaryForm.reset(),
    });
};

const detachClinic = (userId) => {
    if (confirm("Détacher l'agent de la clinique ?")) {
        // Utiliser un objet pour correspondre au paramètre {user} de la route
        router.delete(route('admin.assignments.clinic.detach', { user: userId }));
    }
};

const detachSecretary = (id) => {
    if (confirm("Supprimer la liaison médecin-secrétaire ?")) {
        router.delete(route('admin.assignments.secretary.detach', id));
    }
};
</script>

<template>
    <Head title="Affectations" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-black text-2xl text-slate-800 uppercase italic tracking-tighter">🔗 Gestion des Affectations</h2>
                <Link :href="route('dashboard')" class="text-xs font-black uppercase text-indigo-600 px-4 py-2 bg-indigo-50 rounded-full hover:bg-indigo-100 transition">Retour</Link>
            </div>
        </template>

        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 h-fit">
                        <h3 class="text-lg font-black text-slate-800 uppercase mb-6 italic">1. Agent ↔ Clinique</h3>
                        <form @submit.prevent="submitClinic" class="space-y-6">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Agent</label>
                                <select v-model="clinicForm.user_id" class="w-full mt-1 border-slate-100 rounded-2xl bg-slate-50 focus:ring-indigo-500 font-bold text-sm">
                                    <option value="" disabled>Choisir un agent...</option>
                                    <option v-for="member in staff" :key="member.id" :value="member.id">{{ member.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Clinique</label>
                                <select v-model="clinicForm.clinic_id" class="w-full mt-1 border-slate-100 rounded-2xl bg-slate-50 focus:ring-indigo-500 font-bold text-sm">
                                    <option value="" disabled>Choisir une clinique...</option>
                                    <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">{{ clinic.name }}</option>
                                </select>
                            </div>
                            <button type="submit" :disabled="clinicForm.processing" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 transition-all">Lier à la clinique</button>
                        </form>
                    </div>

                    <div class="lg:col-span-2 bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">Agent</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">Clinique Actuelle</th>
                                    <th class="px-6 py-4 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="member in staff" :key="member.id">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-700">{{ member.name }}</div>
                                        <div class="text-[9px] font-black uppercase text-indigo-400">{{ member.role }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span v-if="member.clinics.length" class="text-emerald-600 font-bold text-sm italic">📍 {{ member.clinics[0].name }}</span>
                                        <span v-else class="text-slate-300 text-xs italic">Aucune</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button v-if="member.clinics.length" @click="detachClinic(member.id)" class="text-rose-500 font-black text-[9px] uppercase hover:underline">Détacher</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="bg-slate-900 p-8 rounded-[2.5rem] shadow-xl text-white h-fit">
                        <h3 class="text-lg font-black uppercase mb-6 italic text-indigo-400">2. Secrétaire ↔ Médecin</h3>
                        <form @submit.prevent="submitSecretary" class="space-y-6">
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Médecin</label>
                                <select v-model="secretaryForm.doctor_id" class="w-full mt-1 border-none rounded-2xl bg-slate-800 focus:ring-indigo-500 font-bold text-sm text-white">
                                    <option value="" disabled>Choisir un médecin...</option>
                                    <option v-for="doc in doctors" :key="doc.id" :value="doc.id">{{ doc.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Secrétaire affectée</label>
                                <select v-model="secretaryForm.secretary_id" class="w-full mt-1 border-none rounded-2xl bg-slate-800 focus:ring-indigo-500 font-bold text-sm text-white">
                                    <option value="" disabled>Choisir une secrétaire...</option>
                                    <option v-for="sec in secretaries" :key="sec.id" :value="sec.id">{{ sec.name }}</option>
                                </select>
                            </div>
                            <button type="submit" :disabled="secretaryForm.processing" class="w-full bg-indigo-500 text-white py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-white hover:text-indigo-600 transition-all">Valider le binôme</button>
                        </form>
                    </div>

                    <div class="lg:col-span-2 bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 bg-slate-50/50 border-b border-slate-100">
                            <h4 class="font-black text-slate-800 uppercase text-xs italic">Binômes Médecin-Secrétaire Actuels</h4>
                        </div>
                        <table class="w-full text-left">
                            <thead class="bg-white border-b border-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">Médecin</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">Secrétaire Associée</th>
                                    <th class="px-6 py-4 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="asgn in currentAssignments" :key="asgn.id">
                                    <td class="px-6 py-4 font-bold text-slate-700">Dr. {{ asgn.doctor_name }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-indigo-600">👤 {{ asgn.secretary_name }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="detachSecretary(asgn.id)" class="text-rose-500 font-black text-[9px] uppercase hover:underline">Supprimer le lien</button>
                                    </td>
                                </tr>
                                <tr v-if="!currentAssignments.length">
                                    <td colspan="3" class="px-6 py-8 text-center text-slate-400 italic text-sm">Aucune liaison enregistrée pour le moment.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>