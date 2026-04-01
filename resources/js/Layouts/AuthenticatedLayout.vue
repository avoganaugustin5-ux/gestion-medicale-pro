<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'Utilisateur', role: '' });

// --- GESTION DES FLASH MESSAGES (Notifications) ---
const flashMessage = computed(() => page.props.flash?.success);
const showNotification = ref(false);

watch(flashMessage, (newMessage) => {
    if (newMessage) {
        showNotification.value = true;
        setTimeout(() => showNotification.value = false, 3000);
    }
});

const isRole = (roleName) => {
    const currentRole = String(user.value?.role || '').toLowerCase().trim();
    return currentRole === String(roleName).toLowerCase().trim();
};
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <Transition
            enter-active-class="transform transition ease-out duration-300"
            enter-from-class="translate-y-[-100%] opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showNotification" class="fixed top-20 right-6 z-50 bg-emerald-600 text-white px-6 py-3 rounded-2xl shadow-2xl font-bold text-sm flex items-center gap-3">
                <span>✅</span> {{ flashMessage }}
            </div>
        </Transition>

        <nav class="bg-white border-b border-slate-200 shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('dashboard')">
                                <ApplicationLogo class="block h-9 w-auto fill-current text-indigo-600" />
                            </Link>
                        </div>

                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">Tableau de bord</NavLink>
                            <NavLink v-if="isRole('medecin')" :href="route('doctor.availabilities.index')" :active="route().current('doctor.availabilities.*')">Mon Planning</NavLink>
                            <NavLink v-if="isRole('admin')" :href="route('admin.users.index')" :active="route().current('admin.users.*')">Utilisateurs</NavLink>
                            <NavLink v-if="isRole('admin')" :href="route('admin.assignments.index')" :active="route().current('admin.assignments.*')">Affectations</NavLink>
                            <NavLink v-if="isRole('admin') || isRole('secretaire')" :href="route('services.index')" :active="route().current('services.*')">Espace Secrétariat</NavLink>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button type="button" class="inline-flex items-center px-3 py-2 text-sm font-bold rounded-md text-slate-600 bg-white hover:text-indigo-600 transition">
                                    {{ user.name }}
                                    <svg class="ms-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Mon Profil</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">Déconnexion</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-white shadow" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main><slot /></main>
    </div>
</template>