<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import debounce from 'lodash/debounce';

// Importation des composants par rôle
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

// --- NORMALISATION DU RÔLE ---
const userRole = computed(() => user.value?.role?.toLowerCase() || 'guest');

const roleLabel = computed(() => {
    const roles = { 
        'admin': 'Administrateur', 
        'medecin': 'Docteur / Praticien', 
        'secretaire': 'Secrétariat Médical', 
        'patient': 'Espace Patient' 
    };
    return roles[userRole.value] || 'Utilisateur';
});

// --- RECHERCHE RÉACTIVE ---
const search = ref(props.filters?.search || '');

watch(search, debounce((value) => {
    router.get(route('dashboard'), { search: value }, { 
        preserveState: true, 
        replace: true 
    });
}, 400));
</script>

<template>
    <Head title="Tableau de Bord" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ roleLabel }}
                </h2>
                
                <div class="flex gap-3">
                    <Link v-if="userRole === 'admin'" :href="route('clinics.create')" 
                        class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase rounded-lg shadow-sm transition-all">
                        <span class="mr-2">➕</span> Nouvelle Clinique
                    </Link>
                    
                    <Link v-if="userRole === 'patient'" :href="route('appointments.create')" 
                        class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase rounded-lg shadow-sm transition-all">
                        <span class="mr-2">📅</span> Prendre RDV
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <transition name="fade">
                    <div v-if="$page.props.flash?.success" 
                        class="mb-8 p-4 bg-white border-l-4 border-emerald-500 shadow-sm rounded-r-xl flex justify-between items-center">
                        <div class="flex items-center text-emerald-800 font-medium">
                            <span class="text-xl mr-3">✨</span> {{ $page.props.flash.success }}
                        </div>
                    </div>
                </transition>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-10 overflow-hidden relative">
                    <div class="relative z-10">
                        <h3 class="text-2xl font-black text-slate-900">Ravi de vous revoir, {{ user?.name }} !</h3>
                        <p class="text-slate-500 mt-2 max-w-2xl">
                            Système de gestion médicale UTS. Gérez vos rendez-vous et vos dossiers médicaux en toute simplicité.
                        </p>
                    </div>
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <span class="text-8xl">🏥</span>
                    </div>
                </div>

                <div v-if="userRole === 'admin'" class="space-y-6 mb-12">
                    <h4 class="text-xs font-black text-indigo-500 uppercase tracking-[0.2em] mb-4">📊 Activité Globale du Réseau</h4>
                    <SecretaryDashboard :appointments="appointments" />
                </div>

                <div v-else-if="userRole === 'secretaire'" class="mb-12">
                    <SecretaryDashboard :appointments="appointments" />
                </div>

                <div v-else-if="userRole === 'medecin'" class="mb-12">
                    <DoctorDashboard :appointments="appointments" />
                </div>

                <div v-else-if="userRole === 'patient'" class="mb-12">
                    <PatientDashboard :appointments="appointments" />
                </div>

                <div v-if="userRole === 'admin' || userRole === 'patient'" class="mt-16">
                    <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-6">
                        <div>
                            <h4 class="text-xl font-bold text-slate-800">Cliniques partenaires</h4>
                            <p class="text-sm text-slate-500">Recherchez un établissement par nom ou spécialité.</p>
                        </div>
                        
                        <div class="w-full md:w-96">
                            <input v-model="search" type="text" 
                                placeholder="Filtrer les cliniques..." 
                                class="w-full px-5 py-3 rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm"
                            />
                        </div>
                    </div>

                    <div v-if="clinics.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="clinic in clinics" :key="clinic.id" 
                            class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-indigo-50 rounded-xl group-hover:bg-indigo-600 transition-colors">
                                    <span class="text-2xl group-hover:filter group-hover:invert">🏢</span>
                                </div>
                                <span class="px-3 py-1 bg-slate-50 text-slate-500 text-[10px] font-bold uppercase rounded-lg">Actif</span>
                            </div>
                            <h5 class="text-lg font-bold text-slate-900 mb-2">{{ clinic.name }}</h5>
                            <p class="text-sm text-slate-500 line-clamp-2 mb-6">{{ clinic.description || 'Aucune description disponible.' }}</p>
                            
                            <Link :href="route('clinics.show', clinic.id)" 
                                class="w-full inline-flex justify-center items-center py-3 bg-slate-900 text-white text-xs font-bold uppercase rounded-xl hover:bg-indigo-600 transition-all">
                                Explorer l'établissement
                            </Link>
                        </div>
                    </div>

                    <div v-else class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-slate-100">
                        <span class="text-5xl block mb-4">🔍</span>
                        <p class="text-slate-400 font-medium">Aucune clinique ne correspond à votre recherche.</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>