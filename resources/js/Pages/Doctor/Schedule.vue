<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({ 
    events: Array // Format attendu : [{ id, title, start, end, backgroundColor, extendedProps: { is_booked: bool } }]
});

// --- GESTION DES MODALES ---
const showAddModal = ref(false);
const showCancelModal = ref(false);
const selectedSlot = ref(null);

// --- CONFIGURATION DU CALENDRIER ---
const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    locale: frLocale,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    buttonText: {
        today: "Aujourd'hui",
        month: "Mois",
        week: "Semaine",
        day: "Jour"
    },
    events: props.events,
    slotMinTime: '07:00:00',
    slotMaxTime: '19:00:00',
    allDaySlot: false,
    selectable: true,
    height: 'auto',
    nowIndicator: true,
    eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridian: false },
    
    // Sélection d'un nouveau créneau libre
    select: (info) => {
        selectedSlot.value = {
            date: info.startStr.split('T')[0],
            start_time: info.startStr.split('T')[1].substring(0, 5),
            end_time: info.endStr.split('T')[1].substring(0, 5)
        };
        showAddModal.value = true;
    },

    // Clic sur un événement existant
    eventClick: (info) => {
        if (info.event.extendedProps?.is_booked) {
            // Optionnel : Tu pourrais ouvrir une modale de détails du patient ici
            return; 
        }
        selectedSlot.value = { 
            id: info.event.id,
            start: info.event.startStr,
            title: info.event.title 
        };
        showCancelModal.value = true;
    }
}));

// --- FORMULAIRES INERTIA ---
const addForm = useForm({
    date: '',
    start_time: '',
    end_time: ''
});

const cancelForm = useForm({
    status: 'cancelled',
    note: ''
});

const confirmAdd = () => {
    addForm.date = selectedSlot.value.date;
    addForm.start_time = selectedSlot.value.start_time;
    addForm.end_time = selectedSlot.value.end_time;

    addForm.post(route('doctor.availabilities.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            addForm.reset();
        }
    });
};

const confirmCancel = () => {
    cancelForm.patch(route('doctor.availabilities.update', selectedSlot.value.id), {
        onSuccess: () => {
            showCancelModal.value = false;
            cancelForm.reset();
        }
    });
};
</script>

<template>
    <Head title="Mon Planning" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="font-black text-2xl text-slate-900 uppercase italic tracking-tighter">📅 Gestion du Planning</h2>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Plateforme AKASUTS • Université Thomas SANKARA</p>
                </div>
                <a :href="route('doctor.availabilities.export')" target="_blank" 
                    class="bg-rose-600 px-8 py-3 text-white text-[10px] font-black uppercase rounded-2xl shadow-xl shadow-rose-100 hover:bg-rose-700 hover:-translate-y-1 transition-all flex items-center gap-3">
                    <span class="text-lg">📄</span> Exporter la semaine (PDF)
                </a>
            </div>
        </template>

        <div class="py-10 bg-slate-50/50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex gap-6 mb-6 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm inline-flex">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-indigo-500 rounded-full"></span>
                        <span class="text-[10px] font-black uppercase text-slate-600">Libre</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-emerald-500 rounded-full"></span>
                        <span class="text-[10px] font-black uppercase text-slate-600">Réservé</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-rose-500 rounded-full"></span>
                        <span class="text-[10px] font-black uppercase text-slate-600">Annulé</span>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.05)] sm:rounded-[3rem] p-4 md:p-10 border border-slate-100">
                    <FullCalendar :options="calendarOptions" />
                </div>
            </div>
        </div>

        <Modal :show="showAddModal" @close="showAddModal = false">
            <div class="p-8">
                <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-3xl mb-6 mx-auto rotate-3">
                    ➕
                </div>
                <h2 class="text-2xl font-black text-slate-900 text-center uppercase tracking-tighter italic">Ouvrir un créneau ?</h2>
                <p class="mt-4 text-center text-slate-500 font-medium leading-relaxed">
                    Vous allez apparaître comme <span class="text-indigo-600 font-bold">disponible</span> le <br>
                    <span class="bg-slate-100 px-3 py-1 rounded-lg font-black text-slate-800 uppercase text-xs">
                        {{ selectedSlot?.date }} de {{ selectedSlot?.start_time }} à {{ selectedSlot?.end_time }}
                    </span>
                </p>
                
                <div class="mt-10 flex justify-center gap-4">
                    <SecondaryButton @click="showAddModal = false" class="!rounded-2xl">Annuler</SecondaryButton>
                    <button @click="confirmAdd" 
                        :disabled="addForm.processing"
                        class="bg-indigo-600 px-8 py-3 text-white text-xs font-black uppercase rounded-2xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95 disabled:opacity-50">
                        Confirmer l'ouverture
                    </button>
                </div>
            </div>
        </Modal>

        <Modal :show="showCancelModal" @close="showCancelModal = false">
            <div class="p-8">
                <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center text-3xl mb-6 mx-auto -rotate-3">
                    ⚠️
                </div>
                <h2 class="text-2xl font-black text-rose-600 text-center uppercase tracking-tighter italic">Déclarer un imprévu ?</h2>
                <p class="mt-2 text-center text-slate-500 text-sm font-medium">
                    Ce créneau ne sera plus réservable.
                </p>

                <div class="mt-6">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-2 tracking-widest">Motif de l'absence</label>
                    <textarea v-model="cancelForm.note" 
                        class="w-full rounded-[1.5rem] border-slate-200 focus:ring-rose-500 focus:border-rose-500 text-sm p-4 min-h-[100px]" 
                        placeholder="Ex: Urgence UTS, réunion pédagogique..."></textarea>
                </div>

                <div class="mt-8 flex justify-center gap-4">
                    <SecondaryButton @click="showCancelModal = false" class="!rounded-2xl">Retour</SecondaryButton>
                    <DangerButton @click="confirmCancel" 
                        :disabled="cancelForm.processing" 
                        class="!rounded-2xl !px-8 !py-3">
                        Confirmer l'annulation
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style>
/* --- CUSTOM FULLCALENDAR DESIGN --- */
.fc { font-family: 'Inter', sans-serif; }

/* Header & Toolbar */
.fc .fc-toolbar-title { font-weight: 900 !important; text-transform: uppercase; font-style: italic; color: #0f172a; letter-spacing: -0.05em; font-size: 1.5rem; }
.fc .fc-button { background: #ffffff !important; border: 1px solid #e2e8f0 !important; color: #475569 !important; font-weight: 800 !important; text-transform: uppercase !important; font-size: 10px !important; border-radius: 12px !important; padding: 8px 16px !important; transition: all 0.3s ease; }
.fc .fc-button:hover { background: #f8fafc !important; color: #4f46e5 !important; border-color: #4f46e5 !important; }
.fc .fc-button-active { background: #4f46e5 !important; color: #ffffff !important; border-color: #4f46e5 !important; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2) !important; }

/* Grid Styling */
.fc .fc-timegrid-slot { height: 4rem !important; border-bottom: 1px solid #f1f5f9 !important; }
.fc .fc-col-header-cell { background: #f8fafc; padding: 15px 0 !important; border: none !important; }
.fc .fc-col-header-cell-cushion { color: #64748b !important; font-weight: 800 !important; text-transform: uppercase !important; font-size: 11px !important; text-decoration: none !important; }

/* Event Styling */
.fc-v-event { border: none !important; padding: 4px !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important; transition: transform 0.2s ease; }
.fc-v-event:hover { transform: scale(1.02); z-index: 50 !important; }
.fc-event-main { padding: 4px !important; font-weight: 800 !important; text-transform: uppercase !important; font-size: 9px !important; }

/* Selection highlight */
.fc .fc-highlight { background: rgba(79, 70, 229, 0.1) !important; }
</style>