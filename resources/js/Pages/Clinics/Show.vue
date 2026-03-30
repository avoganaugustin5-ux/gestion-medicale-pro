<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import debounce from 'lodash/debounce'; // Si tu ne l'as pas, on peut faire sans, mais c'est mieux

const props = defineProps({
    clinic: Object,
    stats: Object,
    staff: Array,
    filters: Object,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Système de recherche
const search = ref(props.filters.search);

// On "écoute" le champ de recherche et on envoie la requête après 300ms de pause
watch(search, debounce((value) => {
    router.get(route('clinics.show', props.clinic.id), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 300));
</script>

<template>
    <Head :title="'Gestion - ' + clinic.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.3em] mb-1">Administration Établissement</p>
                    <h2 class="font-black text-3xl text-slate-800 uppercase tracking-tight">
                        {{ clinic.name }}
                    </h2>
                </div>
                <Link :href="route('dashboard')" class="inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 rounded-2xl font-bold text-[11px] text-slate-500 uppercase tracking-widest hover:bg-slate-50 transition shadow-sm">
                    ← Dashboard Global
                </Link>
            </div>
        </template>

        <div class="py-12 bg-slate-50/50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between group">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Patients</p>
                            <p class="text-4xl font-black text-slate-900 mt-1">{{ stats.patients_count }}</p>
                        </div>
                        <div class="text-4xl opacity-20 group-hover:opacity-100 transition-opacity">👥</div>
                    </div>
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between group">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Médecins</p>
                            <p class="text-4xl font-black text-slate-900 mt-1">{{ stats.doctors_count }}</p>
                        </div>
                        <div class="text-4xl opacity-20 group-hover:opacity-100 transition-opacity">👨‍⚕️</div>
                    </div>
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between group">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">RDV Jour</p>
                            <p class="text-4xl font-black text-indigo-600 mt-1">{{ stats.appointments_today }}</p>
                        </div>
                        <div class="text-4xl opacity-20 group-hover:opacity-100 transition-opacity">📅</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    </div>

                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-8 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <h3 class="font-black text-slate-800 uppercase text-xs tracking-[0.2em]">Personnel Affecté</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ staff.length }} membres trouvés</p>
                        </div>
                        
                        <div class="relative w-full md:w-72">
                            <input 
                                v-model="search"
                                type="text" 
                                placeholder="Rechercher un membre..."
                                class="w-full pl-12 pr-4 py-3 bg-slate-50 border-none rounded-2xl text-xs font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-300"
                            />
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg">🔍</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Identité</th>
                                    <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Rôle</th>
                                    <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Contact</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="member in staff" :key="member.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="font-black text-slate-700 uppercase text-xs tracking-tight">{{ member.name }}</div>
                                        <div class="text-[10px] text-slate-400 italic">{{ member.email }}</div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest" 
                                              :class="member.role === 'medecin' ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-100 text-slate-600'">
                                            {{ member.role }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right font-medium text-[11px] text-slate-500">
                                        {{ member.numeroTelephone || 'Non renseigné' }}
                                    </td>
                                </tr>
                                <tr v-if="staff.length === 0">
                                    <td colspan="3" class="px-8 py-16 text-center">
                                        <p class="text-slate-300 italic text-sm">Aucun membre ne correspond à votre recherche.</p>
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