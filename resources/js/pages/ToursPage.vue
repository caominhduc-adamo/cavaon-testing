<template>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Tours Management</h1>
            <div>
                <button
                    type="button"
                    class="btn btn-outline-primary mr-2"
                    @click="goToBookings"
                >
                    Bookings
                </button>
                <button
                    type="button"
                    class="btn btn-outline-primary mr-2"
                    @click="goToPassengers"
                >
                    Passengers
                </button>
                <button
                    type="button"
                    class="btn btn-outline-primary mr-2"
                    @click="goToInvoices"
                >
                    Invoices
                </button>
                <button
                    v-if="!isFormRoute"
                    type="button"
                    class="btn btn-success"
                    @click="goToCreate"
                >
                    Create
                </button>
                <button
                    v-if="isEditing && form.status !== 'Public'"
                    type="button"
                    class="btn btn-primary"
                    :disabled="publishing || submitting"
                    @click="publishTour"
                >
                    {{ publishing ? 'Publishing...' : 'Public' }}
                </button>
            </div>
        </div>

        <div v-if="isFormRoute" class="card mb-4">
            <div class="card-header">
                {{ isEditing ? 'Edit tour #' + form.id : 'Create new tour' }}
            </div>
            <div class="card-body">
                <div v-if="loadingForm" class="alert alert-info">
                    Loading tour details...
                </div>
                <div v-if="formError" class="alert alert-danger">
                    {{ formError }}
                </div>

                <form v-if="!loadingForm" @submit.prevent="submitTourForm">
                    <div class="form-group">
                        <label for="tour-name">Name</label>
                        <input
                            id="tour-name"
                            v-model.trim="form.name"
                            type="text"
                            :class="['form-control', { 'is-invalid': getFieldError('name') }]"
                            placeholder="Tour name"
                            required
                        >
                        <div v-if="getFieldError('name')" class="invalid-feedback d-block">
                            {{ getFieldError('name') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tour-description">Description</label>
                        <textarea
                            id="tour-description"
                            v-model.trim="form.description"
                            class="form-control"
                            rows="3"
                            placeholder="Tour description"
                        ></textarea>
                    </div>

                    <div v-if="!isEditing" class="form-group">
                        <label for="tour-status">Status</label>
                        <input
                            id="tour-status"
                            value="Draft"
                            type="text"
                            class="form-control"
                            disabled
                        >
                        <small class="form-text text-muted">
                            Status is Draft by default on creation.
                        </small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0">Tour dates</h5>
                            <button
                                class="btn btn-sm btn-outline-primary"
                                type="button"
                                @click="addTourDateRow"
                            >
                                Add date
                            </button>
                        </div>

                        <div
                            v-if="!form.tour_dates.length"
                            class="alert alert-secondary mb-0"
                        >
                            No dates yet.
                        </div>

                        <div
                            v-for="(tourDate, index) in form.tour_dates"
                            :key="tourDate.local_id"
                            class="border rounded p-3 mb-2"
                        >
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label :for="'start-date-' + index">Start date</label>
                                    <input
                                        :id="'start-date-' + index"
                                        v-model="tourDate.start_date"
                                        type="date"
                                        :class="['form-control', { 'is-invalid': getFieldError('tour_dates.' + index + '.start_date') }]"
                                        :min="todayDate"
                                        required
                                    >
                                    <div v-if="getFieldError('tour_dates.' + index + '.start_date')" class="invalid-feedback d-block">
                                        {{ getFieldError('tour_dates.' + index + '.start_date') }}
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    <label :for="'end-date-' + index">End date</label>
                                    <input
                                        :id="'end-date-' + index"
                                        v-model="tourDate.end_date"
                                        type="date"
                                        :class="['form-control', { 'is-invalid': getFieldError('tour_dates.' + index + '.end_date') }]"
                                        :min="todayDate"
                                        required
                                    >
                                    <div v-if="getFieldError('tour_dates.' + index + '.end_date')" class="invalid-feedback d-block">
                                        {{ getFieldError('tour_dates.' + index + '.end_date') }}
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Status</label>
                                    <select
                                        v-model="tourDate.status"
                                        :class="['form-control', { 'is-invalid': getFieldError('tour_dates.' + index + '.status') }]"
                                    >
                                        <option value="Enabled">Enabled</option>
                                        <option value="Disabled">Disabled</option>
                                    </select>
                                    <div v-if="getFieldError('tour_dates.' + index + '.status')" class="invalid-feedback d-block">
                                        {{ getFieldError('tour_dates.' + index + '.status') }}
                                    </div>
                                </div>
                                <div class="form-group col-md-1 d-flex align-items-end">
                                    <button
                                        class="btn btn-sm btn-outline-danger w-100"
                                        type="button"
                                        @click="removeTourDateRow(index)"
                                    >
                                        x
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-if="getFieldError('tour_dates')" class="invalid-feedback d-block">
                            {{ getFieldError('tour_dates') }}
                        </div>
                    </div>

                    <div class="d-flex">
                        <button type="submit" class="btn btn-success mr-2" :disabled="submitting">
                            {{ submitting ? 'Saving...' : (isEditing ? 'Update tour' : 'Create tour') }}
                        </button>
                        <button
                            v-if="isEditing"
                            type="button"
                            class="btn btn-outline-secondary"
                            :disabled="submitting"
                            @click="goToIndex"
                        >
                            Cancel edit
                        </button>
                        <button
                            v-else
                            type="button"
                            class="btn btn-outline-secondary"
                            :disabled="submitting"
                            @click="goToIndex"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <template v-if="!isFormRoute">
            <div class="form-inline mb-3">
                <input
                    v-model.trim="searchInput"
                    type="text"
                    class="form-control mr-2"
                    placeholder="Search by tour name"
                >
                <select v-model="statusFilter" class="form-control mr-2" @change="submitSearch">
                    <option value="">All statuses</option>
                    <option value="Public">Public</option>
                    <option value="Draft">Draft</option>
                </select>
            </div>

            <div v-if="loading" class="alert alert-info mb-3">
                Loading tours...
            </div>

            <div v-else-if="error" class="alert alert-danger mb-3">
                {{ error }}
            </div>

            <div v-else-if="!tours.length" class="alert alert-secondary mb-3">
                No tours found.
            </div>

            <div v-else class="table-responsive mb-3">
                <table class="table table-striped table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col" style="width: 80px;">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Description</th>
                            <th scope="col" style="width: 110px;">Status</th>
                            <th scope="col">Dates</th>
                            <th scope="col" style="width: 95px;">Action</th>
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
                            <td>{{ tour.status }}</td>
                            <td>
                                <template v-if="tour.tour_dates && tour.tour_dates.length">
                                    <ul class="mb-0 pl-3">
                                        <li
                                            v-for="date in tour.tour_dates"
                                            :key="date.id"
                                        >
                                            {{ formatDateRange(date.start_date, date.end_date) }} ({{ date.status }})
                                        </li>
                                    </ul>
                                </template>
                                <span v-else>-</span>
                            </td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    @click="startEdit(tour)"
                                >
                                    Edit
                                </button>
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
        </template>
    </div>
</template>

<script>
import Swal from 'sweetalert2';

export default {
    name: 'ToursPage',
    data() {
        return {
            searchInput: '',
            statusFilter: '',
            searchDebounceTimer: null,
            loadingForm: false,
            submitting: false,
            publishing: false,
            formError: null,
            validationErrors: {},
            localDateCounter: 0,
            form: this.getDefaultForm(),
        };
    },
    computed: {
        todayDate() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');

            return year + '-' + month + '-' + day;
        },
        isFormRoute() {
            return this.$route.name === 'tours.create' || this.$route.name === 'tours.edit';
        },
        isEditing() {
            return !!this.form.id;
        },
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
                const status = query.status === 'Public' || query.status === 'Draft'
                    ? query.status
                    : '';
                const parsedPage = parseInt(query.page, 10);
                const page = Number.isNaN(parsedPage) || parsedPage < 1 ? 1 : parsedPage;

                this.searchInput = q;
                this.statusFilter = status;
                this.$store.dispatch('fetchTours', { q, status, page });
            },
        },
        '$route': {
            immediate: true,
            handler() {
                this.syncFormFromRoute();
            },
        },
        searchInput(newValue) {
            if (this.isFormRoute) {
                return;
            }

            const routeQuery = typeof this.$route.query.q === 'string' ? this.$route.query.q : '';

            if (newValue === routeQuery) {
                return;
            }

            if (this.searchDebounceTimer) {
                clearTimeout(this.searchDebounceTimer);
            }

            this.searchDebounceTimer = setTimeout(() => {
                this.submitSearch();
            }, 1000);
        },
    },
    methods: {
        getDefaultForm() {
            return {
                id: null,
                name: '',
                description: '',
                status: 'Draft',
                tour_dates: [],
            };
        },
        resetForm() {
            this.form = this.getDefaultForm();
            this.formError = null;
            this.validationErrors = {};
        },
        addTourDateRow() {
            this.localDateCounter += 1;
            this.form.tour_dates.push({
                local_id: this.localDateCounter,
                id: null,
                start_date: '',
                end_date: '',
                status: 'Enabled',
            });
        },
        removeTourDateRow(index) {
            this.form.tour_dates.splice(index, 1);
        },
        buildPayload() {
            return {
                name: this.form.name,
                description: this.form.description || null,
                tour_dates: this.form.tour_dates.map((tourDate) => ({
                    id: tourDate.id || undefined,
                    start_date: tourDate.start_date,
                    end_date: tourDate.end_date,
                    status: tourDate.status,
                })),
            };
        },
        getFieldError(field) {
            const errors = this.validationErrors[field];

            if (Array.isArray(errors) && errors.length) {
                return errors[0];
            }

            return null;
        },
        clearValidationErrors() {
            this.validationErrors = {};
        },
        setValidationErrors(error) {
            if (
                error &&
                error.response &&
                error.response.data &&
                error.response.data.errors &&
                typeof error.response.data.errors === 'object'
            ) {
                this.validationErrors = error.response.data.errors;
                return true;
            }

            return false;
        },
        extractApiError(error, fallbackMessage) {
            if (error.response && error.response.data) {
                const payload = error.response.data;

                if (payload.errors) {
                    const allMessages = Object.keys(payload.errors).reduce((messages, field) => {
                        const fieldMessages = payload.errors[field];

                        if (Array.isArray(fieldMessages)) {
                            return messages.concat(fieldMessages);
                        }

                        return messages;
                    }, []);

                    if (allMessages.length) {
                        return allMessages.join(' ');
                    }
                }

                if (payload.message && payload.message !== 'The given data was invalid.') {
                    return payload.message;
                }
            }

            return fallbackMessage;
        },
        async submitTourForm() {
            this.submitting = true;
            this.formError = null;
            this.clearValidationErrors();

            try {
                const wasEditing = this.isEditing;
                const payload = this.buildPayload();

                if (wasEditing) {
                    await window.axios.put('/api/v1/tours/' + this.form.id, payload);
                } else {
                    await window.axios.post('/api/v1/tours', payload);
                }

                this.resetForm();
                await this.$store.dispatch('fetchTours', {
                    q: this.searchInput,
                    status: this.statusFilter,
                    page: this.currentPage,
                });
                await Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: wasEditing ? 'Tour updated successfully.' : 'Tour created successfully.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                this.goToIndex();
            } catch (error) {
                const hasFieldErrors = this.setValidationErrors(error);
                const errorMessage = this.extractApiError(
                    error,
                    'Unable to save tour. Please review your inputs and try again.'
                );

                if (!hasFieldErrors) {
                    this.formError = errorMessage;
                }
                await Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: errorMessage,
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                });
            } finally {
                this.submitting = false;
            }
        },
        async publishTour() {
            if (!this.form.id) {
                return;
            }

            const confirmResult = await Swal.fire({
                title: 'Publish this tour?',
                text: 'This will change the tour status to Public.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, publish',
                cancelButtonText: 'Cancel',
            });

            if (!confirmResult.isConfirmed) {
                return;
            }

            this.publishing = true;
            this.formError = null;

            try {
                const response = await window.axios.patch('/api/v1/tours/' + this.form.id + '/publish');
                const tour = response.data && response.data.data ? response.data.data : null;
                const message = response.data && response.data.message
                    ? response.data.message
                    : 'Tour published successfully.';

                if (tour) {
                    this.fillFormFromTour(tour);
                }

                await this.$store.dispatch('fetchTours', {
                    q: this.searchInput,
                    status: this.statusFilter,
                    page: this.currentPage,
                });
                await Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: message,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            } catch (error) {
                const errorMessage = this.extractApiError(
                    error,
                    'Unable to publish tour. Please review requirements and try again.'
                );
                this.formError = errorMessage;
                await Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: errorMessage,
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                });
            } finally {
                this.publishing = false;
            }
        },
        startEdit(tour) {
            this.$router.push({
                name: 'tours.edit',
                params: { id: String(tour.id) },
                query: this.$route.query,
            });
        },
        fillFormFromTour(tour) {
            this.formError = null;
            this.form = {
                id: tour.id,
                name: tour.name,
                description: tour.description || '',
                status: tour.status || 'Draft',
                tour_dates: (tour.tour_dates || []).map((tourDate) => {
                    this.localDateCounter += 1;

                    return {
                        local_id: this.localDateCounter,
                        id: tourDate.id,
                        start_date: tourDate.start_date || '',
                        end_date: tourDate.end_date || '',
                        status: tourDate.status || 'Enabled',
                    };
                }),
            };
        },
        async fetchTourForEdit(routeId) {
            this.loadingForm = true;
            this.formError = null;
            this.form = this.getDefaultForm();

            try {
                const response = await window.axios.get('/api/v1/tours/' + routeId);
                const tour = response.data && response.data.data ? response.data.data : null;

                if (!tour) {
                    this.formError = 'Tour not found.';
                    return;
                }

                this.fillFormFromTour(tour);
            } catch (error) {
                this.formError = this.extractApiError(
                    error,
                    'Unable to load this tour. Please return to list and try again.'
                );
            } finally {
                this.loadingForm = false;
            }
        },
        syncFormFromRoute() {
            if (!this.isFormRoute) {
                this.resetForm();
                return;
            }

            if (this.$route.name === 'tours.create') {
                this.resetForm();
                return;
            }

            const routeId = parseInt(this.$route.params.id, 10);

            if (Number.isNaN(routeId)) {
                this.resetForm();
                this.formError = 'Invalid tour id.';
                return;
            }

            if (this.form.id === routeId) {
                return;
            }

            this.fetchTourForEdit(routeId);
        },
        goToCreate() {
            this.$router.push({
                name: 'tours.create',
                query: this.$route.query,
            });
        },
        goToBookings() {
            this.$router.push({ name: 'bookings.index' });
        },
        goToPassengers() {
            this.$router.push({ name: 'passengers.index' });
        },
        goToInvoices() {
            this.$router.push({ name: 'invoices.index' });
        },
        goToIndex() {
            this.$router.push({
                name: 'tours.index',
                query: this.$route.query,
            });
        },
        submitSearch() {
            this.$router.push({
                name: 'tours.index',
                query: {
                    q: this.searchInput || undefined,
                    status: this.statusFilter || undefined,
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
                    status: this.statusFilter || undefined,
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
    beforeDestroy() {
        if (this.searchDebounceTimer) {
            clearTimeout(this.searchDebounceTimer);
        }
    },
};
</script>
