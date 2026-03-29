<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import debounce from 'lodash/debounce';

import PatientDashboard from './DashboardPartials/PatientDashboard.vue';
import SecretaryDashboard from './DashboardPartials/SecretaryDashboard.vue';
import DoctorDashboard from './DashboardPartials/DoctorDashboard.vue';

const props = defineProps({
    clinics: Array,
    appointments: Array,
    filters: Object
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

// --- MODIFICATION PRO : Normalisation du rôle ---
const userRole = computed(() => user.value?.role?.toLowerCase() || 'guest');

const roleLabel = computed(() => {
    const roles = { 
        'admin': 'Administrateur', 
        'medecin': 'Docteur', 
        'secretaire': 'Secrétaire', 
        'patient': 'Patient' 
    };
    return roles[userRole.value] || 'Utilisateur';
});
// ------------------------------------------------

const search = ref(props.filters?.search || '');

watch(search, debounce((value) => {
    router.get(route('dashboard'), { search: value }, { 
        preserveState: true, 
        replace: true 
    });
}, 300));

watch(() => page.props.flash, (newFlash) => {
    if (newFlash?.message || newFlash?.success) {
        setTimeout(() => {
            if(page.props.flash) {
                page.props.flash.message = null;
                page.props.flash.success = null;
            }
        }, 5000);
    }
}, { deep: true });
</script>

<template>
    <Head title="Tableau de bord" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Espace {{ roleLabel }}
                </h2>
                
                <div class="flex gap-2">
                    <Link v-if="userRole === 'admin'" :href="route('clinics.create')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                        + Ajouter une clinique
                    </Link>
                    <Link v-if="userRole === 'patient'" :href="route('appointments.create')" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                        Prendre un rendez-vous
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <transition name="fade">
                    <div v-if="$page.props.flash?.success || $page.props.flash?.message" 
                        class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 shadow-md rounded-r flex justify-between items-center">
                        <div class="flex items-center">
                            <span class="mr-2">✅</span>
                            <p class="text-sm font-bold">{{ $page.props.flash.success || $page.props.flash.message }}</p>
                        </div>
                        <button @click="$page.props.flash.message = null" class="text-green-900 font-bold">&times;</button>
                    </div>
                </transition>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8 border-l-4 border-blue-600">
                    <div class="p-6 text-gray-900 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold uppercase tracking-tight text-blue-900">Bienvenue, {{ user?.name }} !</h3>
                            <p class="text-xs text-gray-500 font-medium italic mt-1">
                                Accès sécurisé au portail de santé de Thomas Sankara University.
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="px-4 py-1 bg-blue-50 text-blue-700 text-[10px] font-black rounded-full uppercase border border-blue-100">
                                {{ roleLabel }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="userRole === 'patient'" class="mb-10">
                    <PatientDashboard :appointments="appointments" />
                </div>

                <div v-if="userRole === 'secretaire'" class="mb-10">
                    <SecretaryDashboard :appointments="appointments" />
                </div>

                <div v-if="userRole === 'medecin'" class="mb-10">
                    <DoctorDashboard :appointments="appointments" />
                </div>

                <div v-if="userRole === 'admin' || userRole === 'patient'" class="mt-12">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest px-1">
                            🏥 Répertoire des Cliniques
                        </h4>
                        
                        <div class="relative w-full md:w-80">
                            <input 
                                v-model="search"
                                type="text" 
                                placeholder="Rechercher par nom ou description..." 
                                class="block w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition"
                            />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div v-if="clinics && clinics.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="clinic in clinics" :key="clinic.id" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:border-blue-400 hover:shadow-md transition group">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-md font-bold text-gray-800 group-hover:text-blue-700">{{ clinic.name }}</h3>
                                <div class="h-2 w-2 rounded-full bg-green-500"></div>
                            </div>
                            <p class="text-xs text-gray-500 line-clamp-3 mb-6">{{ clinic.description || 'Établissement agréé UTS.' }}</p>
                            <div class="pt-4 border-t border-gray-50 flex justify-end">
                                <Link :href="route('clinics.show', clinic.id)" class="text-[10px] font-black uppercase text-blue-600 hover:text-blue-800 flex items-center">
                                    Voir la clinique <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ms-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="text-center py-20 bg-white rounded-2xl border-2 border-dashed border-gray-100">
                        <p class="text-gray-400 font-medium">Aucun résultat trouvé pour votre recherche.</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>