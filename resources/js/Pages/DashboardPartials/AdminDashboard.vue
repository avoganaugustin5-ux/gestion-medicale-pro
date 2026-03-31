<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    clinics: Array,
    stats: Object // Reçoit les stats globales (total_users, total_appointments, etc.)
});

const truncate = (text, length) => {
    return text && text.length > length ? text.substring(0, length) + "..." : text;
};
</script>

<template>
    <div class="space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl text-xl">🏢</div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cliniques</p>
                    <p class="text-xl font-black text-slate-900">{{ clinics.length }}</p>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl text-xl">👥</div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Utilisateurs</p>
                    <p class="text-xl font-black text-slate-900">{{ stats?.total_users || 0 }}</p>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="p-3 bg-amber-50 text-amber-600 rounded-xl text-xl">📅</div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total RDV</p>
                    <p class="text-xl font-black text-slate-900">{{ stats?.total_appointments || 0 }}</p>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="p-3 bg-rose-50 text-rose-600 rounded-xl text-xl">🚀</div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Aujourd'hui</p>
                    <p class="text-xl font-black text-slate-900">{{ stats?.today_appointments || 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/30">
                <div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Répertoire des Cliniques</h3>
                    <p class="text-xs text-slate-500 font-medium italic">Gestion du réseau hospitalier UTS.</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('clinics.create')" 
                        class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all active:scale-95">
                        + Nouvelle Clinique
                    </Link>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Établissement</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Capacité (Patients)</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date Création</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="clinic in clinics" :key="clinic.id" class="hover:bg-indigo-50/20 transition-colors group">
                            <td class="px-6 py-5">
                                <p class="font-black text-slate-900 text-sm">{{ clinic.name }}</p>
                                <p class="text-[10px] text-slate-400 italic">{{ truncate(clinic.description, 40) }}</p>
                            </td>
                            <td class="px-6 py-5">
                                <span class="bg-slate-100 px-3 py-1 rounded-full text-[10px] font-bold text-slate-600">
                                    {{ clinic.patients_count || 0 }} patients inscrits
                                </span>
                            </td>
                            <td class="px-6 py-5 text-xs text-slate-500 font-mono">
                                {{ new Date(clinic.created_at).toLocaleDateString('fr-FR') }}
                            </td>
                            <td class="px-6 py-5 text-right">
                                <Link :href="route('clinics.show', clinic.id)" 
                                    class="inline-flex items-center px-4 py-1.5 bg-white border border-slate-200 text-indigo-600 rounded-lg font-black text-[10px] uppercase tracking-tighter hover:bg-indigo-600 hover:text-white transition-all">
                                    Gérer
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="clinics.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                                Aucun établissement enregistré pour le moment.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>