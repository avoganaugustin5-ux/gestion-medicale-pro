<script setup>
import { computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';

const page = usePage();

/**
 * Récupération sécurisée de l'utilisateur.
 * Si auth.user est manquant, on renvoie un objet par défaut pour éviter les erreurs "undefined".
 */
const user = computed(() => {
    return page.props.auth?.user || { name: 'Utilisateur', role: '' };
});

/**
 * Fonction de vérification de rôle ultra-robuste.
 * Elle gère les cas où le rôle serait nul ou écrit différemment (ex: "Admin" vs "admin").
 */
const isRole = (roleName) => {
    const currentRole = String(user.value?.role || '').toLowerCase().trim();
    const targetRole = String(roleName).toLowerCase().trim();
    return currentRole === targetRole;
};

// Debug console : permet de voir exactement ce que le site reçoit comme rôle au chargement
onMounted(() => {
    if (!page.props.auth?.user) {
        console.warn("Attention : Aucune donnée utilisateur reçue via Inertia.");
    } else {
        console.log("Utilisateur connecté :", user.value.name, "| Rôle :", user.value.role);
    }
});
</script>

<template>
    <div class="min-h-screen bg-slate-50">
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
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Tableau de bord
                            </NavLink>

                            <NavLink 
                                v-if="isRole('admin')" 
                                :href="route('admin.users.index')"
                                :active="route().current('admin.users.*')"
                            >
                                Gestion Utilisateurs
                            </NavLink>

                            <NavLink 
                                v-if="isRole('admin') || isRole('secretaire')"
                                :href="route('services.index')" 
                                :active="route().current('services.*')"
                            >
                                Espace Secrétariat
                            </NavLink>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <div class="ms-3 relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold rounded-md text-slate-600 bg-white hover:text-indigo-600 focus:outline-none transition">
                                            {{ user.name }}
                                            <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')"> Mon Profil </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button"> Déconnexion </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-white shadow" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>