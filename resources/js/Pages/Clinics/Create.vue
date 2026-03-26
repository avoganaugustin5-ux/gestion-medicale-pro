<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    description: '',
});

const submit = () => {
    form.post(route('clinics.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Ajouter une clinique" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nouvelle Clinique</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white p-6 shadow sm:rounded-lg border border-gray-100">
                    
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Nom de la clinique</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                            :class="{ 'border-red-500': form.errors.name }"
                            required
                        >
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>

                    <div class="mt-4">
                        <label class="block font-medium text-sm text-gray-700">Description</label>
                        <textarea 
                            v-model="form.description" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                            :class="{ 'border-red-500': form.errors.description }"
                            rows="3"
                        ></textarea>
                        <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                    </div>

                    <div class="mt-6 flex items-center justify-end space-x-4">
                        <Link :href="route('dashboard')" class="text-sm text-gray-600 hover:underline">
                            Annuler
                        </Link>
                        
                        <button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50 transition"
                        >
                            <span v-if="form.processing">Traitement...</span>
                            <span v-else>Enregistrer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>