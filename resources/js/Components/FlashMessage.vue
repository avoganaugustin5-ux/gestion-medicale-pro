<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';

const page = usePage();
const flash = computed(() => page.props.flash);
const show = ref(false);

watch(flash, (newVal) => {
    if (newVal?.success || newVal?.error || newVal?.message) {
        show.value = true;
        setTimeout(() => show.value = false, 4000); // Disparaît après 4s
    }
}, { deep: true, immediate: true });
</script>

<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show && (flash.success || flash.message)" 
             class="fixed top-5 right-5 z-50 max-w-sm w-full bg-emerald-500 text-white shadow-2xl rounded-2xl p-4 flex items-center border border-white/20">
            <span class="mr-3 text-xl">✅</span>
            <p class="text-sm font-bold">{{ flash.success || flash.message }}</p>
        </div>
    </Transition>
</template>