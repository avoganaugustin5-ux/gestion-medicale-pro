<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    users: Array,
    clinics: Array,
    filters: Object
});

const search = ref(props.filters?.search || '');

watch(search, debounce((value) => {
    router.get(route('admin.users.index'), { search: value }, { 
        preserveState: true, 
        replace: true 
    });
}, 300));

const updateRole = (userId, newRole) => {
    router.patch(route('admin.users.updateRole', userId), { role: newRole }, {
        preserveScroll: true,
    });
};

const assignClinic = (userId, clinicId) => {
    router.patch(route('admin.users.updateClinic', userId), { clinic_id: clinicId });
};
</script>

<template>
    <Head title="Gestion du Personnel" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-black text-xl text-slate-800 uppercase italic">👥 Utilisateurs & Rôles</h2>
                
                <div class="relative w-64">
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Rechercher..." 
                        class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 text-sm focus:ring-indigo-500"
                    />
                    <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
                </div>
            </div>
        </template>

        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-slate-100">
                    <div class="p-0 text-gray-900">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Utilisateur</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Rôle Actuel</th>
                                    <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-50">
                                <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-700">{{ user.name }}</div>
                                        <div class="text-xs text-slate-400">{{ user.email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="user.role === 'admin' ? 'bg-rose-100 text-rose-700' : 'bg-indigo-100 text-indigo-700'" class="px-3 py-1 rounded-full text-[9px] font-black uppercase">
                                            {{ user.role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right flex justify-end gap-3">
                                        <select 
                                            @change="updateRole(user.id, $event.target.value)"
                                            class="text-xs rounded-xl border-slate-200 bg-slate-50 font-bold focus:ring-indigo-500"
                                        >
                                            <option value="admin" :selected="user.role === 'admin'">Admin</option>
                                            <option value="medecin" :selected="user.role === 'medecin'">Médecin</option>
                                            <option value="secretaire" :selected="user.role === 'secretaire'">Secrétaire</option>
                                            <option value="patient" :selected="user.role === 'patient'">Patient</option>
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