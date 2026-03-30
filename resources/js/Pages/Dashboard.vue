<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import debounce from 'lodash/debounce';

// Importation des composants par rôle
import AdminDashboard from './DashboardPartials/AdminDashboard.vue'; // Mis à jour
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
const userRole = computed(() => user.value?.role?.toLowerCase() || 'guest');

const roleLabel = computed(() => {
    const roles = { 
        'admin': 'Administration Centrale', 
        'medecin': 'Espace Praticien', 
        'secretaire': 'Gestion Secrétariat', 
        'patient': 'Mon Espace Santé' 
    };
    return roles[userRole.value] || 'Utilisateur';
});

const search = ref(props.filters?.search || '');
watch(search, debounce((value) => {
    router.get(route('dashboard'), { search: value }, { preserveState: true, replace: true });
}, 400));
</script>

<template>
    <Head title="Tableau de Bord" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">
                    {{ roleLabel }}
                </h2>
                
                <div class="flex gap-3">
                    <Link v-if="userRole === 'admin'" :href="route('clinics.create')" 
                        class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase rounded-xl shadow-lg shadow-indigo-200 transition-all active:scale-95">
                        <span class="mr-2">➕</span> Nouvelle Clinique
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-10 bg-slate-50/50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <transition name="fade">
                    <div v-if="$page.props.flash?.success" 
                        class="mb-8 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 shadow-sm rounded-r-xl flex items-center">
                        <span class="text-xl mr-3">✅</span> {{ $page.props.flash.success }}
                    </div>
                </transition>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 mb-10 relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-black text-slate-900">Ravi de vous revoir, {{ user?.name }} !</h3>
                        <p class="text-slate-500 mt-2 max-w-2xl font-medium">
                            Plateforme de gestion hospitalière UTS. Accédez à vos outils de pilotage en un clic.
                        </p>
                    </div>
                    <div class="absolute -top-4 -right-4 opacity-5 pointer-events-none">
                        <span class="text-[12rem]">🏥</span>
                    </div>
                </div>

                <div class="space-y-10">
                    <AdminDashboard v-if="userRole === 'admin'" :clinics="clinics" />
                    
                    <SecretaryDashboard v-else-if="userRole === 'secretaire'" :appointments="appointments" />
                    
                    <DoctorDashboard v-else-if="userRole === 'medecin'" :appointments="appointments" />
                    
                    <PatientDashboard v-else-if="userRole === 'patient'" :appointments="appointments" />
                </div>

                <div v-if="userRole === 'admin' || userRole === 'patient'" class="mt-20 border-t border-slate-200 pt-10">
                    <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-6">
                        <div>
                            <h4 class="text-2xl font-black text-slate-800">Exploration du Réseau</h4>
                            <p class="text-slate-500 font-medium">Rechercher un établissement spécifique au sein de l'université.</p>
                        </div>
                        <div class="w-full md:w-96">
                            <input v-model="search" type="text" placeholder="Nom de la clinique..." 
                                class="w-full px-6 py-4 rounded-2xl border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm font-medium" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>