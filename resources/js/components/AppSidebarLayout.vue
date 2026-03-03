<template>
    <div class="d-flex min-vh-100">
        <aside class="border-right bg-light" style="width: 260px;">
            <div class="p-3 border-bottom">
                <h5 class="mb-0">Management</h5>
            </div>

            <nav class="list-group list-group-flush rounded-0">
                <router-link
                    v-for="item in menuItems"
                    :key="item.name"
                    :to="{ name: item.name }"
                    class="list-group-item list-group-item-action border-0"
                    active-class="active"
                    exact-active-class="active"
                >
                    {{ item.label }}
                </router-link>
            </nav>
        </aside>

        <main class="flex-grow-1 py-4">
            <router-view />
        </main>
    </div>
</template>

<script>
export default {
    name: 'AppSidebarLayout',
    data() {
        return {
            menuItems: [
                { name: 'tours.index', label: 'Tour Management' },
                { name: 'bookings.index', label: 'Booking Management' },
                { name: 'passengers.index', label: 'Passenger Management' },
                { name: 'invoices.index', label: 'Invoice Management' },
            ],
        };
    },
};
</script>
