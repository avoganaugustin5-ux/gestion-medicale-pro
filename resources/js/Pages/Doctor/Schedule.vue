<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';

const props = defineProps({ events: Array });

const calendarOptions = {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    locale: frLocale,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: props.events,
    slotMinTime: '07:00:00',
    slotMaxTime: '19:00:00',
    allDaySlot: false,
    editable: false,
    selectable: true,
    select: (info) => handleSelect(info),
    eventClick: (info) => handleEventClick(info)
};

const form = useForm({
    slots: []
});

const handleSelect = (info) => {
    const startTime = info.startStr.split('T')[1].substring(0, 5);
    const endTime = info.endStr.split('T')[1].substring(0, 5);
    const date = info.startStr.split('T')[0];

    if(confirm(`Ajouter ce créneau : ${date} de ${startTime} à ${endTime} ?`)) {
        form.slots = [{ date, start_time: startTime, end_time: endTime }];
        form.post(route('doctor.availabilities.store'));
    }
};

const handleEventClick = (info) => {
    const isBooked = info.event.extendedProps.is_booked;
    
    if (isBooked) {
        alert("Ce créneau est déjà réservé par un patient. Contactez la secrétaire pour une annulation manuelle.");
        return;
    }

    if (confirm("Marquer ce créneau comme imprévu (Annulé) ? Cela enverra une alerte à votre secrétaire.")) {
        const id = info.event.id;
        useForm({ status: 'cancelled', note: 'Imprévu médical' })
            .patch(route('doctor.availabilities.update', id));
    }
};
</script>

<template>
    <Head title="Mon Planning" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-black text-xl text-slate-800 uppercase italic">📅 Gestion du Planning</h2>
                <a 
                    :href="route('doctor.availabilities.pdf')" 
                    target="_blank"
                    class="bg-rose-600 px-6 py-2 text-white text-xs font-black uppercase rounded-full shadow-lg hover:bg-rose-700 transition flex items-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exporter en PDF
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl p-8 border border-slate-100">
                    <div class="mb-6 flex flex-wrap gap-6 items-center justify-between">
                        <div class="flex gap-4">
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 bg-emerald-500 rounded-md shadow-sm"></span> 
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">Disponible</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 bg-blue-500 rounded-md shadow-sm"></span> 
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">Occupé (RDV)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 bg-red-500 rounded-md shadow-sm"></span> 
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">Annulé / Imprévu</span>
                            </div>
                        </div>
                        <p class="text-xs font-bold text-slate-400">Cliquez sur un créneau vide pour l'ajouter, ou sur un événement pour l'annuler.</p>
                    </div>
                    
                    <div class="calendar-container">
                        <FullCalendar :options="calendarOptions" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.calendar-container {
    background: white;
    padding: 10px;
}
.fc { max-height: 800px; font-family: 'Inter', sans-serif; }
.fc-toolbar-title { font-weight: 900 !important; text-transform: uppercase; font-style: italic; color: #1e293b; }
.fc-event { cursor: pointer; border-radius: 6px; padding: 3px; border: none !important; transition: transform 0.1s; }
.fc-event:hover { transform: scale(1.02); }
.fc-v-event { background-color: var(--fc-event-bg-color); }
.fc-timegrid-slot { height: 3em !important; border-bottom: 1px solid #f1f5f9 !important; }
</style>