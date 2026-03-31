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
    if (confirm("Marquer ce créneau comme imprévu (Annulé) ?")) {
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
            <h2 class="font-black text-xl text-slate-800 uppercase italic">📅 Mon Planning Hebdomadaire</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl p-6">
                    <div class="mb-4 flex gap-4">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-emerald-500 rounded-full"></span> <span class="text-xs font-bold text-slate-500">Disponible</span></div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-blue-500 rounded-full"></span> <span class="text-xs font-bold text-slate-500">Occupé (RDV)</span></div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-red-500 rounded-full"></span> <span class="text-xs font-bold text-slate-500">Annulé / Imprévu</span></div>
                    </div>
                    
                    <FullCalendar :options="calendarOptions" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.fc { max-height: 700px; font-family: inherit; }
.fc-toolbar-title { font-weight: 900 !important; text-transform: uppercase; font-style: italic; }
.fc-event { cursor: pointer; border-radius: 8px; padding: 2px; }
</style>