<script setup>
const props = defineProps({
    appointments: Array
});
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-blue-900 text-white flex justify-between items-center">
            <h4 class="font-bold flex items-center">
                <span class="mr-2">🩺</span> Mes Consultations du Jour
            </h4>
            <span class="bg-blue-700 text-[10px] px-3 py-1 rounded-full font-bold border border-blue-500">
                {{ appointments.length }} PATIENT(S)
            </span>
        </div>

        <div class="p-4 grid grid-cols-1 gap-4">
            <div v-for="app in appointments" :key="app.id" 
                class="flex items-center justify-between p-4 rounded-lg border-l-4 border-blue-600 bg-gray-50 hover:bg-white hover:shadow-md transition border-y border-r border-gray-100">
                
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-100 p-3 rounded-full text-blue-700 font-bold text-sm">
                        {{ app.date_rdv.split(' ')[1] || '--:--' }}
                    </div>
                    <div>
                        <p class="font-black text-gray-900">{{ app.patient?.user?.name }}</p>
                        <p class="text-[10px] text-blue-600 font-bold uppercase">{{ app.service?.nom }}</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 text-[10px] font-bold rounded-md hover:bg-gray-100 transition uppercase">
                        Dossier
                    </button>
                    <button class="px-3 py-1.5 bg-blue-600 text-white text-[10px] font-bold rounded-md hover:bg-blue-700 transition uppercase">
                        Terminer
                    </button>
                </div>
            </div>

            <div v-if="appointments.length === 0" class="text-center py-10">
                <p class="text-gray-400 italic text-sm font-medium">Aucune consultation prévue pour le moment.</p>
            </div>
        </div>
    </div>
</template>