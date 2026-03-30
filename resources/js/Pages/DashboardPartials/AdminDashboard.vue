<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    clinics: Array,
    stats: Object 
});

const truncate = (text, length) => {
    return text && text.length > length ? text.substring(0, length) + "..." : text;
};
</script>

<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl text-2xl">🏢</div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Cliniques</p>
                    <p class="text-2xl font-bold text-gray-900">{{ clinics.length }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Répertoire des Cliniques</h3>
                    <p class="text-sm text-gray-500">Gérez les établissements de santé du réseau UTS.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link 
                        :href="route('admin.assignments.index')" 
                        class="inline-flex items-center px-4 py-2 bg-slate-100 border border-slate-200 rounded-lg font-bold text-[10px] text-slate-700 uppercase tracking-widest hover:bg-slate-200 transition duration-150"
                    >
                        🔗 Gérer les Affectations
                    </Link>
                    
                    <Link 
                        :href="route('clinics.create')" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-bold text-[10px] text-white uppercase tracking-widest hover:bg-indigo-700 transition duration-150 shadow-md"
                    >
                        + Nouvelle Clinique
                    </Link>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Établissement</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Description</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Date Création</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="clinic in clinics" :key="clinic.id" class="hover:bg-gray-50/80 transition-colors group">
                            <td class="px-6 py-4 font-bold text-gray-900">{{ clinic.name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ truncate(clinic.description, 50) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-400 italic">
                                {{ new Date(clinic.created_at).toLocaleDateString('fr-FR') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link 
                                    :href="route('clinics.show', clinic.id)" 
                                    class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase"
                                >
                                    Gérer
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="clinics.length === 0">
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">Aucune clinique.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>