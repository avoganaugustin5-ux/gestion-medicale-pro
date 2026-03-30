<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    clinic: Object,
    patient: Object,
    consultations: Array,
    appointments: Array
});

const page = usePage();
const userRole = computed(() => page.props.auth.user.role);
const showForm = ref(false);
const searchQuery = ref('');

// Formulaire nouvelle consultation
const form = useForm({
    diagnostic: '',
    prescription: '',
});

// Formulaire d'édition
const editingId = ref(null);
const editForm = useForm({
    diagnostic: '',
    prescription: '',
});

const filteredConsultations = computed(() => {
    return props.consultations.filter(c => 
        c.diagnostic.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        c.prescription?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const submitConsultation = () => {
    form.post(route('clinics.patients.consultations.store', [props.clinic.id, props.patient.id]), {
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
};

const startEdit = (c) => {
    editingId.value = c.id;
    editForm.diagnostic = c.diagnostic;
    editForm.prescription = c.prescription;
};

const updateConsultation = (id) => {
    editForm.patch(route('consultations.update', [props.clinic.id, props.patient.id, id]), {
        onSuccess: () => editingId.value = null
    });
};

const deleteConsultation = (id) => {
    if(confirm('Supprimer cette ordonnance définitivement ?')) {
        router.delete(route('consultations.destroy', [props.clinic.id, props.patient.id, id]));
    }
};

const printPage = () => {
    window.print();
};
</script>

<template>
    <Head :title="'Dossier - ' + patient.last_name" />

    <AuthenticatedLayout>
        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-5xl mx-auto px-4 space-y-8">
                
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-indigo-200 text-white">👤</div>
                        <div>
                            <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">{{ patient.last_name }} {{ patient.first_name }}</h2>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1 italic">📞 {{ patient.phone }}</p>
                        </div>
                    </div>
                    <div v-if="userRole === 'medecin' || userRole === 'admin'" class="flex gap-2">
                        <button @click="showForm = !showForm" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200">
                            {{ showForm ? 'Fermer' : '+ Nouvelle Consultation' }}
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <input v-model="searchQuery" type="text" placeholder="Rechercher un diagnostic ou un médicament..." 
                           class="w-full bg-white border-none rounded-2xl shadow-sm px-6 py-4 text-xs font-bold focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div v-if="showForm" class="bg-white p-8 rounded-[2.5rem] shadow-xl border-2 border-indigo-500/20">
                    <h3 class="font-black text-slate-800 uppercase text-xs tracking-widest mb-6">Rapport de Consultation</h3>
                    <form @submit.prevent="submitConsultation" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Diagnostic & Observations</label>
                                <textarea v-model="form.diagnostic" rows="4" class="w-full rounded-2xl border-none bg-slate-50 focus:ring-4 focus:ring-indigo-500/10 font-bold text-xs" required></textarea>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Prescription (Médicaments)</label>
                                <textarea v-model="form.prescription" rows="4" class="w-full rounded-2xl border-none bg-slate-50 focus:ring-4 focus:ring-indigo-500/10 font-bold text-xs"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-900 transition-all">
                                Enregistrer le rapport
                            </button>
                        </div>
                    </form>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1 space-y-6">
                        <h3 class="font-black text-slate-800 uppercase text-[10px] tracking-[0.2em] ml-4">Historique RDV</h3>
                        <div v-for="app in appointments" :key="app.id" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[8px] font-black uppercase rounded-full">{{ app.status }}</span>
                            <p class="text-xs font-black text-slate-700 mt-3">{{ new Date(app.date_heure).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' }) }}</p>
                        </div>
                    </div>

                    <div class="lg:col-span-2 space-y-6">
                        <h3 class="font-black text-slate-800 uppercase text-[10px] tracking-[0.2em] ml-4">Dossier Médical</h3>
                        
                        <div v-if="userRole === 'medecin' || userRole === 'admin'">
                            <div v-for="cons in filteredConsultations" :key="cons.id" class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm mb-6 relative group">
                                
                                <div class="absolute top-6 right-8 flex gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a :href="route('consultations.pdf', [clinic.id, patient.id, cons.id])" class="text-lg" title="Télécharger PDF">📥</a>
                                    <button @click="startEdit(cons)" class="text-lg" title="Modifier">✏️</button>
                                    <button @click="printPage" class="text-lg" title="Imprimer">🖨️</button>
                                    <button @click="deleteConsultation(cons.id)" class="text-lg" title="Supprimer">🗑️</button>
                                </div>

                                <div class="mb-6">
                                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Le {{ new Date(cons.created_at).toLocaleDateString() }}</p>
                                    <p class="text-[10px] font-black text-indigo-600 uppercase italic mt-1">Dr. {{ cons.doctor?.user?.name || 'Inconnu' }}</p>
                                </div>
                                
                                <div v-if="editingId === cons.id" class="space-y-4">
                                    <textarea v-model="editForm.diagnostic" class="w-full rounded-xl bg-slate-50 border-none text-xs font-bold p-4" rows="3"></textarea>
                                    <textarea v-model="editForm.prescription" class="w-full rounded-xl bg-slate-50 border-none text-xs italic p-4" rows="3"></textarea>
                                    <div class="flex gap-2 justify-end">
                                        <button @click="editingId = null" class="text-[10px] font-black uppercase text-slate-400 mr-4">Annuler</button>
                                        <button @click="updateConsultation(cons.id)" class="bg-indigo-600 text-white px-6 py-2 rounded-xl text-[10px] font-black uppercase">Mettre à jour</button>
                                    </div>
                                </div>

                                <div v-else class="space-y-6">
                                    <div>
                                        <h4 class="font-black text-slate-800 text-[10px] uppercase tracking-widest mb-2 italic text-indigo-400">Observations</h4>
                                        <p class="text-slate-600 text-sm leading-relaxed font-medium">{{ cons.diagnostic }}</p>
                                    </div>
                                    <div v-if="cons.prescription" class="bg-slate-50 p-6 rounded-[1.5rem] border-l-4 border-indigo-500">
                                        <h4 class="font-black text-slate-800 text-[9px] uppercase tracking-widest mb-2">Ordonnance</h4>
                                        <p class="text-slate-600 text-xs italic font-bold leading-relaxed">{{ cons.prescription }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="bg-amber-50 border border-amber-100 p-12 rounded-[2.5rem] text-center">
                            <span class="text-4xl block mb-4">🔒</span>
                            <p class="text-amber-700 text-[10px] font-black uppercase tracking-[0.2em]">Accès restreint au corps médical</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>