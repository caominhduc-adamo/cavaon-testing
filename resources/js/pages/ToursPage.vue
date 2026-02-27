<template>
    <div class="container">
        <h1 class="mb-4">Tours</h1>

        <form class="form-inline mb-3" @submit.prevent="submitSearch">
            <input
                v-model.trim="searchInput"
                type="text"
                class="form-control mr-2"
                placeholder="Search by tour name"
            >
            <button type="submit" class="btn btn-primary" :disabled="loading">
                Search
            </button>
        </form>

        <div v-if="loading" class="alert alert-info mb-3">
            Loading tours...
        </div>

        <div v-else-if="error" class="alert alert-danger mb-3">
            {{ error }}
        </div>

        <div v-else-if="!tours.length" class="alert alert-secondary mb-3">
            No public tours found.
        </div>

        <div v-else class="table-responsive mb-3">
            <table class="table table-striped table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th scope="col" style="width: 80px;">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Description</th>
                        <th scope="col">Enabled dates</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="tour in tours"
                        :key="tour.id"
                    >
                        <td>{{ tour.id }}</td>
                        <td>{{ tour.name }}</td>
                        <td>{{ tour.description || '-' }}</td>
                        <td>
                            <template v-if="tour.tour_dates && tour.tour_dates.length">
                                <ul class="mb-0 pl-3">
                                    <li
                                        v-for="date in tour.tour_dates"
                                        :key="date.id"
                                    >
                                        {{ formatDateRange(date.start_date, date.end_date) }}
                                    </li>
                                </ul>
                            </template>
                            <span v-else>-</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav v-if="hasPagination" aria-label="Tours pagination">
            <ul class="pagination">
                <li class="page-item" :class="{ disabled: isFirstPage || loading }">
                    <button class="page-link" type="button" @click="goToPage(currentPage - 1)">
                        Previous
                    </button>
                </li>
                <li class="page-item disabled">
                    <span class="page-link">
                        Page {{ currentPage }} of {{ lastPage }}
                    </span>
                </li>
                <li class="page-item" :class="{ disabled: isLastPage || loading }">
                    <button class="page-link" type="button" @click="goToPage(currentPage + 1)">
                        Next
                    </button>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
export default {
    name: 'ToursPage',
    data() {
        return {
            searchInput: '',
        };
    },
    computed: {
        tours() {
            return this.$store.state.items;
        },
        loading() {
            return this.$store.state.loading;
        },
        error() {
            return this.$store.state.error;
        },
        currentPage() {
            return this.$store.state.pagination.current_page;
        },
        lastPage() {
            return this.$store.state.pagination.last_page;
        },
        hasPagination() {
            return this.$store.state.pagination.total > this.$store.state.pagination.per_page;
        },
        isFirstPage() {
            return this.currentPage <= 1;
        },
        isLastPage() {
            return this.currentPage >= this.lastPage;
        },
    },
    watch: {
        '$route.query': {
            immediate: true,
            handler(query) {
                const q = typeof query.q === 'string' ? query.q : '';
                const parsedPage = parseInt(query.page, 10);
                const page = Number.isNaN(parsedPage) || parsedPage < 1 ? 1 : parsedPage;

                this.searchInput = q;
                this.$store.dispatch('fetchTours', { q, page });
            },
        },
    },
    methods: {
        submitSearch() {
            this.$router.push({
                name: 'tours.index',
                query: {
                    q: this.searchInput || undefined,
                    page: 1,
                },
            });
        },
        goToPage(page) {
            if (page < 1 || page > this.lastPage || page === this.currentPage) {
                return;
            }

            this.$router.push({
                name: 'tours.index',
                query: {
                    q: this.searchInput || undefined,
                    page,
                },
            });
        },
        formatDateRange(startDate, endDate) {
            if (!endDate) {
                return startDate;
            }

            return startDate + ' to ' + endDate;
        },
    },
};
</script>
