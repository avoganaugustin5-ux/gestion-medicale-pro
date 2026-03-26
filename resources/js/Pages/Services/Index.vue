<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    services: Array,
});

const form = useForm({
    nom: '',
    categorie: '',
});

const submit = () => {
    form.post(route('services.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Gestion des Services" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Services Médicaux</h2>
        </template>

        <div class="py-12 px-4">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="bg-white p-6 shadow rounded-lg h-fit">
                    <h3 class="text-lg font-bold mb-4">Nouveau Service</h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium">Nom du Service</label>
                            <input v-model="form.nom" type="text" class="w-full rounded-md border-gray-300" placeholder="ex: Cardiologie">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Catégorie</label>
                            <select v-model="form.categorie" class="w-full rounded-md border-gray-300">
                                <option value="A">Catégorie A</option>
                                <option value="B">Catégorie B</option>
                                <option value="C">Catégorie C</option>
                            </select>
                        </div>
                        <button class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">Créer</button>
                    </form>
                </div>

                <div class="md:col-span-2 bg-white shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase">Catégorie</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="service in services" :key="service.id">
                                <td class="px-6 py-4">{{ service.nom }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-gray-100 rounded text-sm font-mono">{{ service.categorie }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>