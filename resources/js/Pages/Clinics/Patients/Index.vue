<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    clinic: Object,
    patients: Array,
    filters: Object,
});

const form = useForm({
    first_name: '',
    last_name: '',
    phone: '',
});

// Logique de recherche
const search = ref(props.filters.search || '');

// Fonction debounce pour limiter les requêtes SQL pendant la saisie
function customDebounce(fn, delay) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}

watch(search, customDebounce((value) => {
    router.get(route('clinics.patients.index', props.clinic.id), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 400));

const submit = () => {
    form.post(route('clinics.patients.store', props.clinic.id), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head :title="'Patients - ' + clinic.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.3em] mb-1 italic">Base de données UTS</p>
                    <h2 class="font-black text-3xl text-slate-800 uppercase tracking-tight">
                        Répertoire Patients
                    </h2>
                </div>
                <Link :href="route('clinics.show', clinic.id)" class="inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 rounded-2xl font-bold text-[11px] text-slate-500 uppercase tracking-widest hover:bg-slate-50 transition shadow-sm">
                    ← Retour Gestion
                </Link>
            </div>
        </template>

        <div class="py-12 bg-slate-50/50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 group">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-2xl">📝</span>
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400">Nouveau Patient</h3>
                    </div>
                    
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Nom de famille</label>
                            <input v-model="form.last_name" type="text" placeholder="ex: SANDAOGO" class="w-full rounded-2xl border-none bg-slate-50 focus:ring-4 focus:ring-indigo-500/10 font-bold text-sm py-3" required />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Prénom</label>
                            <input v-model="form.first_name" type="text" placeholder="ex: Augustin" class="w-full rounded-2xl border-none bg-slate-50 focus:ring-4 focus:ring-indigo-500/10 font-bold text-sm py-3" required />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Téléphone</label>
                            <input v-model="form.phone" type="text" placeholder="+226 00 00 00 00" class="w-full rounded-2xl border-none bg-slate-50 focus:ring-4 focus:ring-indigo-500/10 font-bold text-sm py-3" required />
                        </div>
                        <button type="submit" :disabled="form.processing" class="w-full bg-slate-900 text-white py-3.5 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-indigo-600 transition-all shadow-lg shadow-indigo-500/10 disabled:opacity-50">
                            Enregistrer
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-8 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
                        <div>
                            <h3 class="font-black text-slate-800 uppercase text-xs tracking-[0.2em]">Patients de la clinique</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ patients.length }} dossiers actifs</p>
                        </div>
                        
                        <div class="relative w-full md:w-80 group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg">🔍</span>
                            <input 
                                v-model="search"
                                type="text" 
                                placeholder="Chercher un patient..."
                                class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border-none rounded-2xl text-xs font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-300"
                            />
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-8 py-5 text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Patient</th>
                                    <th class="px-8 py-5 text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Contact</th>
                                    <th class="px-8 py-5 text-[9px] font-black text-slate-400 uppercase tracking-widest italic text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="patient in patients" :key="patient.id" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-8 py-6">
                                        <div class="font-black text-slate-700 uppercase text-xs tracking-tight">
                                            {{ patient.last_name }} <span class="text-indigo-600">{{ patient.first_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-xs font-bold text-slate-500">
                                        {{ patient.phone }}
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <button class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl font-black text-[9px] uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-all">
                                            Ouvrir Dossier
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="patients.length === 0">
                                    <td colspan="3" class="px-8 py-16 text-center">
                                        <div class="text-4xl mb-2 opacity-20">📂</div>
                                        <p class="text-slate-300 italic text-sm font-medium">Aucun patient trouvé.</p>
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