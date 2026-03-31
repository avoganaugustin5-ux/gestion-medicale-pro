<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    clinic: Object,
});

const form = useForm({
    name: props.clinic.name,
    description: props.clinic.description,
});

const submit = () => {
    // On utilise patch comme défini dans ton web.php
    form.patch(route('clinics.update', props.clinic.id));
};
</script>

<template>
    <Head :title="'Modifier - ' + clinic.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('clinics.show', clinic.id)" class="text-slate-400 hover:text-indigo-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">Mise à jour : {{ clinic.name }}</h2>
            </div>
        </template>

        <div class="py-12 bg-slate-50/50 min-h-screen">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                    <div class="p-8 border-b border-slate-50 bg-slate-50/30">
                        <h3 class="font-bold text-lg text-slate-800">Modifier les informations</h3>
                        <p class="text-sm text-slate-500">Mettez à jour les détails de l'établissement pour le réseau UTS.</p>
                    </div>

                    <form @submit.prevent="submit" class="p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Nom de la Clinique</label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                class="w-full px-5 py-4 rounded-2xl border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all font-medium" 
                                :class="{ 'border-red-500 bg-red-50': form.errors.name }"
                            >
                            <p v-if="form.errors.name" class="text-red-500 text-xs font-bold mt-1 ml-2">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Description / Services</label>
                            <textarea 
                                v-model="form.description" 
                                class="w-full px-5 py-4 rounded-2xl border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all font-medium" 
                                :class="{ 'border-red-500 bg-red-50': form.errors.description }"
                                rows="5"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-red-500 text-xs font-bold mt-1 ml-2">{{ form.errors.description }}</p>
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-4">
                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-slate-900 disabled:opacity-50 transition-all shadow-lg shadow-indigo-200 active:scale-95"
                            >
                                <span v-if="form.processing">Mise à jour...</span>
                                <span v-else>Enregistrer les modifications</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>