<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    availabilities: Array
});

const form = useForm({
    date: '',
    start_time: '',
    end_time: '',
});

const submit = () => {
    form.post(route('doctor.availabilities.store'), {
        onSuccess: () => form.reset(),
    });
};

const deleteAvailability = (id) => {
    if (confirm('Supprimer ce créneau ?')) {
        useForm({}).delete(route('doctor.availabilities.destroy', id));
    }
};

const printPlanning = () => {
    window.print();
};
</script>

<template>
    <Head title="Mon Planning" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-black text-2xl text-slate-800 uppercase italic">📅 Gestion du Planning</h2>
                
                <button 
                    @click="printPlanning" 
                    class="no-print hidden md:inline-flex items-center px-6 py-2.5 bg-slate-800 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-900 transition-all active:scale-95 shadow-lg shadow-slate-200"
                >
                    <span class="mr-2 text-base">🖨️</span> Imprimer le planning
                </button>
            </div>
        </template>

        <div class="py-12 bg-slate-50 min-h-screen print:bg-white print:py-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="only-print w-full text-center mb-10 border-b-2 border-slate-900 pb-6">
                    <h1 class="text-3xl font-black uppercase tracking-tighter">Université Thomas Sankara</h1>
                    <p class="text-lg font-bold text-slate-600 uppercase italic">Service Médical - Planning des Disponibilités</p>
                    <div class="mt-4 flex justify-between items-end">
                        <p class="text-sm font-bold">Médecin : <span class="uppercase">{{ $page.props.auth.user.name }}</span></p>
                        <p class="text-[10px] font-medium italic">Document généré le {{ new Date().toLocaleDateString('fr-FR') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-1 no-print">
                        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                            <h3 class="text-lg font-black text-slate-800 mb-6 uppercase tracking-tighter">Ajouter un créneau</h3>
                            <form @submit.prevent="submit" class="space-y-5">
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Date</label>
                                    <input type="date" v-model="form.date" class="w-full border-slate-100 rounded-xl bg-slate-50 font-bold focus:ring-indigo-500" required />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Début</label>
                                        <input type="time" v-model="form.start_time" class="w-full border-slate-100 rounded-xl bg-slate-50 font-bold focus:ring-indigo-500" required />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Fin</label>
                                        <input type="time" v-model="form.end_time" class="w-full border-slate-100 rounded-xl bg-slate-50 font-bold focus:ring-indigo-500" required />
                                    </div>
                                </div>
                                <button type="submit" :disabled="form.processing" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black uppercase text-xs shadow-lg shadow-indigo-100 transition-all active:scale-95">
                                    Enregistrer la disponibilité
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-2 print:w-full">
                        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden print:border-none print:shadow-none">
                            <div class="p-8 border-b border-slate-50 bg-slate-50/30 no-print">
                                <h3 class="text-lg font-black text-slate-800 uppercase tracking-tighter italic">Mes disponibilités à venir</h3>
                            </div>
                            <div class="divide-y divide-slate-50 print:divide-slate-200">
                                <div v-for="slot in availabilities" :key="slot.id" class="p-6 flex items-center justify-between hover:bg-slate-50/50 transition-colors print:px-0">
                                    <div class="flex items-center gap-6">
                                        <div class="bg-indigo-50 p-4 rounded-2xl text-center min-w-[80px] print:bg-white print:border print:border-slate-200">
                                            <span class="block text-[10px] font-black text-indigo-400 uppercase">Jour</span>
                                            <span class="block font-black text-indigo-700">{{ slot.date }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-slate-700 uppercase italic">
                                                {{ slot.start_time }} — {{ slot.end_time }}
                                            </p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 print:text-slate-600">
                                                {{ slot.is_booked ? '🔴 Déjà réservé' : '🟢 Libre' }}
                                            </p>
                                        </div>
                                    </div>
                                    <button @click="deleteAvailability(slot.id)" class="no-print p-3 text-rose-400 hover:bg-rose-50 rounded-xl transition-colors">
                                        🗑️
                                    </button>
                                </div>
                                <div v-if="availabilities.length === 0" class="p-20 text-center text-slate-400 font-medium italic">
                                    Aucun créneau défini pour le moment.
                                </div>
                            </div>
                        </div>
                        <div class="only-print mt-12 flex justify-end">
                            <div class="text-center border-t border-slate-200 pt-4 w-64">
                                <p class="text-[10px] font-black uppercase italic">Signature du Médecin</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.only-print {
    display: none;
}

@media print {
    .no-print, nav, header {
        display: none !important;
    }

    .only-print {
        display: block !important;
    }

    /* Correction des sélecteurs Tailwind avec backslash */
    .lg\:col-span-2 {
        width: 100% !important;
        grid-column: span 3 / span 3 !important;
    }

    .bg-slate-50 {
        background-color: white !important;
    }
    
    .rounded-\[2rem\] {
        border-radius: 0 !important;
    }

    .bg-indigo-50 {
        background-color: #f0f7ff !important;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
}
</style>