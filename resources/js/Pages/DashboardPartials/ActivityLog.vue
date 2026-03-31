<script setup>
defineProps({
    activities: Array,
    role: String
});

const getStatusClass = (action) => {
    if (action.includes('créé') || action.includes('ajouté')) return 'bg-emerald-100 text-emerald-700';
    if (action.includes('supprimé')) return 'bg-rose-100 text-rose-700';
    return 'bg-amber-100 text-amber-700';
};
</script>

<template>
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-8 border-b border-slate-50 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 uppercase italic tracking-tighter">
                {{ role === 'admin' ? 'Journal Global des Activités' : 'Mes Actions Récentes' }}
            </h3>
            <span class="px-4 py-1 bg-slate-100 rounded-full text-[10px] font-black text-slate-500 uppercase">Live</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Utilisateur</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Action</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Cible</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Heure</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr v-for="log in activities" :key="log.id" class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs">👤</div>
                                <span class="font-bold text-slate-700 text-sm">{{ log.user_name }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <span :class="['px-3 py-1 rounded-lg text-[10px] font-black uppercase', getStatusClass(log.action)]">
                                {{ log.action }}
                            </span>
                        </td>
                        <td class="px-8 py-5 text-sm font-medium text-slate-500">{{ log.target }}</td>
                        <td class="px-8 py-5 text-[10px] font-black text-slate-400">{{ log.time }}</td>
                    </tr>
                    <tr v-if="activities.length === 0">
                        <td colspan="4" class="px-8 py-10 text-center text-slate-400 italic text-sm">
                            Aucune activité enregistrée aujourd'hui.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>