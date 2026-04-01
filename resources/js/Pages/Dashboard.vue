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
    stats: Object,
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
                <h2 class="font-black text-2xl text-slate-800 tracking-tight uppercase italic">{{ roleLabel }}</h2>
                <div>
                    <Link v-if="role === 'admin'" :href="route('clinics.create')" class="bg-indigo-600 px-5 py-2.5 text-white text-[10px] font-black uppercase rounded-full shadow-lg hover:bg-indigo-700 transition tracking-widest">
                        + Nouvelle Clinique
                    </Link>
                    <Link v-if="role === 'medecin'" :href="route('doctor.availabilities.index')" class="bg-rose-600 px-5 py-2.5 text-white text-[10px] font-black uppercase rounded-full shadow-lg hover:bg-rose-700 transition tracking-widest">
                        📅 Gérer mon planning
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-10 bg-slate-50/50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-[2.5rem] shadow-sm p-10 mb-8 border border-slate-100 flex justify-between items-center overflow-hidden relative">
                    <div class="relative z-10">
                        <h3 class="text-4xl font-black text-slate-900 uppercase tracking-tighter">Bienvenue, {{ user?.name }}</h3>
                        <p class="text-indigo-600 font-bold italic mt-1">Plateforme AKASUTS • Université Thomas SANKARA</p>
                    </div>
                    <div class="text-6xl opacity-20 absolute -right-4 -bottom-4 rotate-12 select-none">🏥</div>
                </div>

                <div v-if="role !== 'patient'" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border-l-8 border-indigo-500 hover:scale-[1.02] transition-transform">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Aujourd'hui</p>
                        <p class="text-4xl font-black text-slate-800">{{ stats?.today_appointments || 0 }}</p>
                        <p class="text-[10px] font-bold text-slate-500 uppercase mt-2">Rendez-vous programmés</p>
                    </div>
                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border-l-8 border-emerald-500 hover:scale-[1.02] transition-transform">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                            {{ role === 'admin' ? 'Total Établissements' : 'Patients suivis' }}
                        </p>
                        <p class="text-4xl font-black text-slate-800">
                            {{ role === 'admin' ? (stats?.total_clinics || 0) : (stats?.total_patients || 0) }}
                        </p>
                        <p class="text-[10px] font-bold text-slate-500 uppercase mt-2">Données centralisées UTS</p>
                    </div>
                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border-l-8 border-orange-500 hover:scale-[1.02] transition-transform">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Journal de bord</p>
                        <p class="text-4xl font-black text-slate-800">{{ activities?.length || 0 }}</p>
                        <p class="text-[10px] font-bold text-slate-500 uppercase mt-2">Dernières interactions</p>
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