<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

// Accès sécurisé à l'utilisateur via une propriété calculée
const user = computed(() => page.props.auth.user);
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
                                v-if="user?.role === 'admin'" 
                                :href="route('admin.users.index')"
                                :active="route().current('admin.users.*')"
                            >
                                Gestion Utilisateurs
                            </NavLink>

                            <NavLink 
                                v-if="['admin', 'secretaire'].includes(user?.role)"
                                :href="route('services.index')" 
                                :active="route().current('services.*')"
                            >
                                Espace Secrétariat
                            </NavLink>

                            <template v-if="user?.role === 'medecin'">
                                <NavLink :href="route('dashboard')" :active="route().current('doctor.planning')">
                                    📅 Mon Planning
                                </NavLink>
                            </template>

                            <NavLink 
                                v-if="user?.role === 'patient'"
                                :href="route('dashboard')" 
                                :active="route().current('appointments.*')"
                            >
                                Mes Rendez-vous
                            </NavLink>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <div class="ms-3 relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold rounded-md text-slate-600 bg-white hover:text-indigo-600 focus:outline-none transition ease-in-out duration-150">
                                            {{ user?.name || 'Chargement...' }}
                                            <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')"> Mon Profil </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-600"> 
                                        Déconnexion 
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-white shadow-sm border-b border-slate-100" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="animate-fade-in">
            <slot />
        </main>
    </div>
</template>