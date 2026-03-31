<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import debounce from 'lodash/debounce';

// Partials
import AdminDashboard from './DashboardPartials/AdminDashboard.vue';
import PatientDashboard from './DashboardPartials/PatientDashboard.vue';
import SecretaryDashboard from './DashboardPartials/SecretaryDashboard.vue';
import DoctorDashboard from './DashboardPartials/DoctorDashboard.vue';
import ActivityLog from './DashboardPartials/ActivityLog.vue';

const props = defineProps({
    clinics: Array,
    appointments: Array,
    activities: Array,
    stats: Object, // NOUVEAU
    filters: Object,
    userRole: String
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const role = computed(() => props.userRole?.toLowerCase() || 'guest');

const roleLabel = computed(() => {
    const roles = { 
        'admin': 'Administration Centrale', 
        'medecin': 'Espace Praticien', 
        'secretaire': 'Gestion Secrétariat', 
        'patient': 'Mon Espace Santé' 
    };
    return roles[role.value] || 'Utilisateur';
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
            <div class="flex justify-between items-center">
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">
                    {{ roleLabel }}
                </h2>
                <Link v-if="role === 'admin'" :href="route('clinics.create')" 
                    class="bg-indigo-600 px-4 py-2 text-white text-xs font-bold uppercase rounded-xl">
                    + Nouvelle Clinique
                </Link>
            </div>
        </template>

        <div class="py-10 bg-slate-50/50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="bg-white rounded-3xl shadow-sm p-8 mb-8 border border-slate-100">
                    <h3 class="text-3xl font-black text-slate-900 uppercase">Bienvenue, {{ user?.name }}</h3>
                    <p class="text-slate-500 font-medium">Portail UTS Santé : Gestion en temps réel.</p>
                </div>

                <div v-if="role !== 'patient'" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-indigo-500">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Aujourd'hui</p>
                        <p class="text-3xl font-black text-slate-800">{{ stats.today_appointments || 0 }}</p>
                        <p class="text-xs text-slate-500">Rendez-vous programmés</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-emerald-500">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            {{ role === 'admin' ? 'Total Établissements' : 'Patients suivis' }}
                        </p>
                        <p class="text-3xl font-black text-slate-800">
                            {{ role === 'admin' ? stats.total_clinics : stats.total_patients || 0 }}
                        </p>
                        <p class="text-xs text-slate-500">Inscrits sur la plateforme</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-orange-500">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Activités</p>
                        <p class="text-3xl font-black text-slate-800">{{ activities.length }}</p>
                        <p class="text-xs text-slate-500">Dernières actions enregistrées</p>
                    </div>
                </div>

                <div class="space-y-10">
                    <AdminDashboard v-if="role === 'admin'" :clinics="clinics" />
                    <SecretaryDashboard v-else-if="role === 'secretaire'" :appointments="appointments" />
                    <DoctorDashboard v-else-if="role === 'medecin'" :appointments="appointments" />
                    <PatientDashboard v-else-if="role === 'patient'" :appointments="appointments" />
                </div>

                <div v-if="role !== 'patient'" class="mt-12">
                    <ActivityLog :activities="activities" :role="role" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>