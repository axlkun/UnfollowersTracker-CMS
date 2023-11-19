<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import EditBtn from '@/OwnComponents/EditBtn.vue';
import DeleteBtn from '@/OwnComponents/DeleteBtn.vue';
import AppTable from '@/OwnComponents/Table.vue';
import Container from '@/OwnComponents/Container.vue';
import Card from '@/OwnComponents/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BreadCroumbs from '@/OwnComponents/BreadCroumbs.vue';

const props = defineProps({
    users: Object,
});

const headers = [
    { name: "Title" },
    { name: "Client" },
    { name: "Created date" },
    {
        name: "Actions",
        class: "text-right"
    }
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
                        <td>{{ user.title }}</td>
                        <td>{{ user.company }}</td>
                        <td>{{ user.created_at_formated }}</td>
                    </tr>
                </AppTable>
            </Card>
        </Container>

    </AppLayout>
</template>
