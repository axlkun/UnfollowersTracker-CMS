<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppTable from '@/OwnComponents/Table.vue';
import Container from '@/OwnComponents/Container.vue';
import Card from '@/OwnComponents/Card.vue';
import BreadCroumbs from '@/OwnComponents/BreadCroumbs.vue';

const props = defineProps({
    users: Object,
});

const headers = [
    { name: "Username" },
    { name: "Registered date" },
];

const breadcrumbs = [
    {
        label: "Users"
    }
];

</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <BreadCroumbs :items="breadcrumbs"></BreadCroumbs>
        </template>

        <Container>

            <Card class="mt-4">
                <AppTable :headers="headers" :items="users">
                    <tr v-for="user in users.data" :key="user.id">
                        <td>{{ user.username }}</td>
                        <td>{{ user.created_at_formated }}</td>
                    </tr>
                </AppTable>
            </Card>
        </Container>

    </AppLayout>
</template>
