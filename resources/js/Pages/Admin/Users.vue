<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
    clinics: Array
});

const updateRole = (userId, newRole) => {
    useForm({ role: newRole }).patch(route('admin.users.updateRole', userId), {
        preserveScroll: true,
    });
};
const assignClinic = (userId, clinicId) => {
    useForm({ clinic_id: clinicId }).patch(route('admin.users.updateClinic', userId));
};
</script>

<template>
    <Head title="Gestion du Personnel" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestion des Utilisateurs & Rôles</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle Actuel</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Changer le Rôle</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users" :key="user.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ user.email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="user.role === 'admin' ? 'text-red-600 font-bold' : 'text-blue-600'" class="uppercase text-xs">
                                            {{ user.role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <select 
                                            @change="updateRole(user.id, $event.target.value)"
                                            class="text-sm rounded border-gray-300 shadow-sm focus:border-indigo-500"
                                        >
                                            <option value="admin" :selected="user.role === 'admin'">Admin (Fondateur)</option>
                                            <option value="secretary" :selected="user.role === 'secretary'">Secrétaire</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4">
                                        <select 
                                            v-if="user.role === 'secretary'"
                                            @change="assignClinic(user.id, $event.target.value)"
                                            class="text-sm rounded border-gray-300"
    >
                                            <option value="">Aucune clinique</option>
                                            <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id" :selected="user.clinic_id === clinic.id">
                                                {{ clinic.name }}
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>