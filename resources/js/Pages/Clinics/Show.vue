<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    clinic: Object,
    stats: Object,
});

const page = usePage();

// CORRECTION ICI : Ajout du ? pour ne pas planter si auth est absent
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <Head :title="'Gestion - ' + clinic.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Espace d'administration : <span class="text-blue-600">{{ clinic.name }}</span>
                </h2>
                <Link :href="route('dashboard')" class="text-sm text-gray-500 hover:text-gray-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Retour au dashboard global
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                        <p class="text-sm text-gray-500 font-medium uppercase">Patients enregistrés</p>
                        <p class="text-3xl font-bold text-gray-800">{{ stats.patients_count }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                        <p class="text-sm text-gray-500 font-medium uppercase">Médecins actifs</p>
                        <p class="text-3xl font-bold text-gray-800">{{ stats.doctors_count }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-orange-500">
                        <p class="text-sm text-gray-500 font-medium uppercase">RDV prévus aujourd'hui</p>
                        <p class="text-3xl font-bold text-gray-800">{{ stats.appointments_today }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            
                            <Link :href="route('clinics.doctors.index', clinic.id)" class="p-6 border rounded-xl hover:border-blue-500 hover:bg-blue-50 transition cursor-pointer group shadow-sm">
                                <span class="text-3xl mb-4 block">👨‍⚕️</span>
                                <h3 class="font-bold text-gray-800 group-hover:text-blue-600">Médecins</h3>
                                <p class="text-xs text-gray-500 mt-2">Gérer le personnel de santé.</p>
                            </Link>

                            <Link :href="route('clinics.appointments.index', clinic.id)" class="p-6 border rounded-xl hover:border-green-500 hover:bg-green-50 transition cursor-pointer group shadow-sm">
                                <span class="text-3xl mb-4 block">📅</span>
                                <h3 class="font-bold text-gray-800 group-hover:text-green-600">Rendez-vous</h3>
                                <p class="text-xs text-gray-500 mt-2">Planning et disponibilités.</p>
                            </Link>

                            <Link :href="route('clinics.patients.index', clinic.id)" class="p-6 border rounded-xl hover:border-purple-500 hover:bg-purple-50 transition cursor-pointer group shadow-sm">
                                <span class="text-3xl mb-4 block">👥</span>
                                <h3 class="font-bold text-gray-800 group-hover:text-purple-600">Patients</h3>
                                <p class="text-xs text-gray-500 mt-2">Répertoire des patients.</p>
                            </Link>

                            <Link v-if="user?.role === 'admin'" :href="route('admin.users.index')" class="p-6 border rounded-xl hover:border-red-500 hover:bg-red-50 transition cursor-pointer group shadow-sm">
                                <span class="text-3xl mb-4 block">⚙️</span>
                                <h3 class="font-bold text-gray-800 group-hover:text-red-600">Personnel</h3>
                                <p class="text-xs text-gray-500 mt-2">Gérer les rôles et accès.</p>
                            </Link>
                            
                            <div v-else class="p-6 border rounded-xl opacity-50 cursor-not-allowed bg-gray-50 shadow-sm">
                                <span class="text-3xl mb-4 block">🔒</span>
                                <h3 class="font-bold text-gray-400">Paramètres</h3>
                                <p class="text-xs text-gray-400 mt-2">Réservé à l'administrateur.</p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>