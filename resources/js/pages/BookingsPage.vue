<template>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Booking Management</h1>
            <div>
                <button
                    v-if="!isFormRoute"
                    type="button"
                    class="btn btn-success mr-2"
                    @click="goToCreate"
                >
                    Create
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
                    class="btn btn-outline-primary"
                    @click="goToInvoices"
                >
                    Invoices
                </button>
            </div>
        </div>

        <div v-if="isFormRoute" class="card mb-4">
            <div class="card-header">
                {{ isEditing ? 'Edit booking #' + form.id : 'Create new booking' }}
            </div>
            <div class="card-body">
                <div v-if="loadingForm" class="alert alert-info">Loading booking details...</div>
                <div v-if="formError" class="alert alert-danger">{{ formError }}</div>

                <form v-if="!loadingForm" @submit.prevent="submitBookingForm">
                    <div v-if="isEditing" class="form-group">
                        <label>Reference</label>
                        <input class="form-control" type="text" :value="form.reference || '-'" disabled>
                    </div>

                    <div class="form-group">
                        <label for="tour-id">Tour (Public only)</label>
                        <select
                            ref="tourSelect"
                            id="tour-id"
                            v-model.number="form.tour_id"
                            :class="['form-control', { 'is-invalid': getFieldError('tour_id') }]"
                            required
                            @change="onTourChanged"
                        >
                            <option :value="null" disabled>Select a tour</option>
                            <option v-for="tour in tours" :key="tour.id" :value="tour.id">
                                #{{ tour.id }} - {{ tour.name }}
                            </option>
                        </select>
                        <div v-if="getFieldError('tour_id')" class="invalid-feedback d-block">
                            {{ getFieldError('tour_id') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tour-date-id">Tour date (Enabled only)</label>
                        <select
                            ref="tourDateSelect"
                            id="tour-date-id"
                            v-model.number="form.tour_date_id"
                            :class="['form-control', { 'is-invalid': getFieldError('tour_date_id') }]"
                            required
                        >
                            <option :value="null" disabled>Select a tour date</option>
                            <option v-for="tourDate in availableTourDates" :key="tourDate.id" :value="tourDate.id">
                                #{{ tourDate.id }} - {{ formatDateRange(tourDate.start_date, tourDate.end_date) }}
                            </option>
                        </select>
                        <div v-if="getFieldError('tour_date_id')" class="invalid-feedback d-block">
                            {{ getFieldError('tour_date_id') }}
                        </div>
                        <small class="form-text text-muted">
                            Only Enabled dates are listed.
                        </small>
                    </div>

                    <div v-if="isEditing" class="form-group">
                        <label for="booking-status">Status</label>
                        <select
                            id="booking-status"
                            v-model="form.status"
                            :class="['form-control', { 'is-invalid': getFieldError('status') }]"
                        >
                            <option value="Submitted">Submitted</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <div v-if="getFieldError('status')" class="invalid-feedback d-block">
                            {{ getFieldError('status') }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0">Passengers</h5>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary"
                                @click="togglePassengerCreator"
                            >
                                {{ showPassengerCreator ? 'Close passenger form' : 'Add new passenger' }}
                            </button>
                        </div>

                        <div v-if="showPassengerCreator" class="border rounded p-3 mb-3">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label :for="'quick-passenger-first-name-' + (isEditing ? form.id : 'new')">First name</label>
                                    <input
                                        :id="'quick-passenger-first-name-' + (isEditing ? form.id : 'new')"
                                        v-model.trim="newPassengerForm.first_name"
                                        type="text"
                                        :class="['form-control', { 'is-invalid': getNewPassengerFieldError('first_name') }]"
                                    >
                                    <div v-if="getNewPassengerFieldError('first_name')" class="invalid-feedback d-block">
                                        {{ getNewPassengerFieldError('first_name') }}
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label :for="'quick-passenger-last-name-' + (isEditing ? form.id : 'new')">Last name</label>
                                    <input
                                        :id="'quick-passenger-last-name-' + (isEditing ? form.id : 'new')"
                                        v-model.trim="newPassengerForm.last_name"
                                        type="text"
                                        :class="['form-control', { 'is-invalid': getNewPassengerFieldError('last_name') }]"
                                    >
                                    <div v-if="getNewPassengerFieldError('last_name')" class="invalid-feedback d-block">
                                        {{ getNewPassengerFieldError('last_name') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label :for="'quick-passenger-email-' + (isEditing ? form.id : 'new')">Email</label>
                                    <input
                                        :id="'quick-passenger-email-' + (isEditing ? form.id : 'new')"
                                        v-model.trim="newPassengerForm.email"
                                        type="email"
                                        :class="['form-control', { 'is-invalid': getNewPassengerFieldError('email') }]"
                                    >
                                    <div v-if="getNewPassengerFieldError('email')" class="invalid-feedback d-block">
                                        {{ getNewPassengerFieldError('email') }}
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label :for="'quick-passenger-phone-' + (isEditing ? form.id : 'new')">Phone</label>
                                    <input
                                        :id="'quick-passenger-phone-' + (isEditing ? form.id : 'new')"
                                        v-model.trim="newPassengerForm.phone"
                                        type="text"
                                        :class="['form-control', { 'is-invalid': getNewPassengerFieldError('phone') }]"
                                    >
                                    <div v-if="getNewPassengerFieldError('phone')" class="invalid-feedback d-block">
                                        {{ getNewPassengerFieldError('phone') }}
                                    </div>
                                </div>
                            </div>
                            <div v-if="newPassengerError" class="alert alert-danger py-2">
                                {{ newPassengerError }}
                            </div>
                            <div class="d-flex">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary mr-2"
                                    :disabled="creatingPassenger"
                                    @click="submitQuickPassenger"
                                >
                                    {{ creatingPassenger ? 'Adding...' : 'Add passenger to this booking' }}
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    :disabled="creatingPassenger"
                                    @click="resetQuickPassengerForm"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <input
                                v-model.trim="passengerSearchInput"
                                type="text"
                                class="form-control"
                                placeholder="Filter passengers by name/email/phone"
                            >
                        </div>

                        <div class="border rounded p-2" style="max-height: 280px; overflow-y: auto;">
                            <div v-if="passengersLoading" class="text-muted">Loading passengers...</div>
                            <div v-else-if="!filteredPassengers.length" class="text-muted">
                                No passengers available.
                            </div>
                            <div v-else>
                                <div
                                    v-for="passenger in filteredPassengers"
                                    :key="passenger.id"
                                    class="form-check mb-2"
                                >
                                    <input
                                        :id="'passenger-' + passenger.id"
                                        class="form-check-input"
                                        type="checkbox"
                                        :value="passenger.id"
                                        v-model="form.passenger_ids"
                                    >
                                    <label class="form-check-label" :for="'passenger-' + passenger.id">
                                        #{{ passenger.id }} - {{ passenger.first_name }} {{ passenger.last_name }}
                                        <span class="text-muted">
                                            ({{ passenger.email || passenger.phone || 'No contact' }}, {{ passenger.status }})
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div v-if="getFieldError('passenger_ids')" class="invalid-feedback d-block">
                            {{ getFieldError('passenger_ids') }}
                        </div>
                        <div v-if="getFieldError('passenger_ids.0')" class="invalid-feedback d-block">
                            {{ getFieldError('passenger_ids.0') }}
                        </div>
                    </div>

                    <div class="d-flex">
                        <button type="submit" class="btn btn-success mr-2" :disabled="submitting">
                            {{ submitting ? 'Saving...' : (isEditing ? 'Update booking' : 'Create booking') }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary" :disabled="submitting" @click="goToIndex">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <template v-if="!isFormRoute">
            <div class="form-inline mb-3">
                <input
                    v-model.trim="referenceInput"
                    type="text"
                    class="form-control mr-2"
                    placeholder="Search by booking reference"
                >
                <select v-model="statusFilter" class="form-control mr-2" @change="submitSearch">
                    <option value="">All statuses</option>
                    <option value="Submitted">Submitted</option>
                    <option value="Confirmed">Confirmed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>

            <div v-if="loadingList" class="alert alert-info mb-3">Loading bookings...</div>
            <div v-else-if="listError" class="alert alert-danger mb-3">{{ listError }}</div>
            <div v-else-if="!items.length" class="alert alert-secondary mb-3">No bookings found.</div>

            <div v-else class="table-responsive mb-3">
                <table class="table table-striped table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>Reference</th>
                            <th>Tour</th>
                            <th>Tour Date</th>
                            <th>Invoice</th>
                            <th style="width: 100px;">Passengers</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.id">
                            <td>{{ item.id }}</td>
                            <td>{{ item.reference }}</td>
                            <td>{{ item.tour ? item.tour.name : '-' }}</td>
                            <td>
                                {{
                                    item.tour_date
                                        ? formatDateRange(item.tour_date.start_date, item.tour_date.end_date)
                                        : '-'
                                }}
                            </td>
                            <td>
                                <template v-if="item.invoice">
                                    <a
                                        href="#"
                                        @click.prevent="goToInvoiceEdit(item.invoice.id)"
                                    >
                                        {{ item.invoice.invoice_number }}
                                    </a>
                                    <span> ({{ item.invoice.status }})</span>
                                </template>
                                <span v-else>-</span>
                            </td>
                            <td>{{ item.passengers ? item.passengers.length : 0 }}</td>
                            <td>{{ item.status }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" type="button" @click="startEdit(item)">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav v-if="hasPagination" aria-label="Bookings pagination">
                <ul class="pagination">
                    <li class="page-item" :class="{ disabled: isFirstPage || loadingList }">
                        <button class="page-link" type="button" @click="goToPage(currentPage - 1)">Previous</button>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">Page {{ currentPage }} of {{ lastPage }}</span>
                    </li>
                    <li class="page-item" :class="{ disabled: isLastPage || loadingList }">
                        <button class="page-link" type="button" @click="goToPage(currentPage + 1)">Next</button>
                    </li>
                </ul>
            </nav>
        </template>
    </div>
</template>

<script>
import Swal from 'sweetalert2';

export default {
    name: 'BookingsPage',
    data() {
        return {
            items: [],
            tours: [],
            passengers: [],
            loadingList: false,
            passengersLoading: false,
            listError: null,
            loadingForm: false,
            submitting: false,
            formError: null,
            validationErrors: {},
            referenceInput: '',
            statusFilter: '',
            passengerSearchInput: '',
            showPassengerCreator: false,
            creatingPassenger: false,
            newPassengerError: null,
            newPassengerValidationErrors: {},
            newPassengerForm: this.getDefaultNewPassengerForm(),
            searchDebounceTimer: null,
            pagination: {
                current_page: 1,
                last_page: 1,
                per_page: 10,
                total: 0,
            },
            form: this.getDefaultForm(),
        };
    },
    computed: {
        isFormRoute() {
            return this.$route.name === 'bookings.create' || this.$route.name === 'bookings.edit';
        },
        isEditing() {
            return !!this.form.id;
        },
        currentPage() {
            return this.pagination.current_page;
        },
        lastPage() {
            return this.pagination.last_page;
        },
        hasPagination() {
            return this.pagination.total > this.pagination.per_page;
        },
        isFirstPage() {
            return this.currentPage <= 1;
        },
        isLastPage() {
            return this.currentPage >= this.lastPage;
        },
        selectedTour() {
            if (!this.form.tour_id) {
                return null;
            }

            return this.tours.find((tour) => Number(tour.id) === Number(this.form.tour_id)) || null;
        },
        availableTourDates() {
            if (!this.selectedTour || !Array.isArray(this.selectedTour.tour_dates)) {
                return [];
            }

            return this.selectedTour.tour_dates.filter((tourDate) => tourDate.status === 'Enabled');
        },
        filteredPassengers() {
            const keyword = this.passengerSearchInput.toLowerCase();

            if (!keyword) {
                return this.passengers;
            }

            return this.passengers.filter((passenger) => {
                const fullName = (passenger.first_name + ' ' + passenger.last_name).toLowerCase();
                const email = (passenger.email || '').toLowerCase();
                const phone = (passenger.phone || '').toLowerCase();

                return fullName.includes(keyword) || email.includes(keyword) || phone.includes(keyword);
            });
        },
    },
    watch: {
        '$route.query': {
            immediate: true,
            handler(query) {
                const reference = typeof query.reference === 'string' ? query.reference : '';
                const status = ['Submitted', 'Confirmed', 'Cancelled'].includes(query.status) ? query.status : '';
                const parsedPage = parseInt(query.page, 10);
                const page = Number.isNaN(parsedPage) || parsedPage < 1 ? 1 : parsedPage;

                this.referenceInput = reference;
                this.statusFilter = status;
                this.fetchBookings({ reference, status, page });
            },
        },
        '$route': {
            immediate: true,
            handler() {
                this.syncFormFromRoute();
            },
        },
        referenceInput(newValue) {
            if (this.isFormRoute) {
                return;
            }

            const routeQuery = typeof this.$route.query.reference === 'string' ? this.$route.query.reference : '';

            if (newValue === routeQuery) {
                return;
            }

            if (this.searchDebounceTimer) {
                clearTimeout(this.searchDebounceTimer);
            }

            this.searchDebounceTimer = setTimeout(() => {
                this.submitSearch();
            }, 800);
        },
        availableTourDates() {
            this.$nextTick(() => {
                this.initTourDateSelect2();
                this.syncTourDateSelect2Value();
            });
        },
        'form.tour_id'() {
            this.$nextTick(() => {
                this.syncTourSelect2Value();
            });
        },
        'form.tour_date_id'() {
            this.$nextTick(() => {
                this.syncTourDateSelect2Value();
            });
        },
    },
    created() {
        this.fetchToursForSelection();
        this.fetchPassengersForSelection();
    },
    mounted() {
        this.$nextTick(() => {
            this.initTourSelect2();
            this.initTourDateSelect2();
            this.syncTourSelect2Value();
            this.syncTourDateSelect2Value();
        });
    },
    methods: {
        getDefaultForm() {
            return {
                id: null,
                reference: '',
                tour_id: null,
                tour_date_id: null,
                status: 'Submitted',
                passenger_ids: [],
            };
        },
        getDefaultNewPassengerForm() {
            return {
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
            };
        },
        getFieldError(field) {
            const errors = this.validationErrors[field];
            return Array.isArray(errors) && errors.length ? errors[0] : null;
        },
        getNewPassengerFieldError(field) {
            const errors = this.newPassengerValidationErrors[field];
            return Array.isArray(errors) && errors.length ? errors[0] : null;
        },
        clearValidationErrors() {
            this.validationErrors = {};
        },
        clearNewPassengerValidationErrors() {
            this.newPassengerValidationErrors = {};
        },
        setValidationErrors(error) {
            if (error && error.response && error.response.data && error.response.data.errors) {
                this.validationErrors = error.response.data.errors;
                return true;
            }

            return false;
        },
        setNewPassengerValidationErrors(error) {
            if (error && error.response && error.response.data && error.response.data.errors) {
                this.newPassengerValidationErrors = error.response.data.errors;
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
                        return Array.isArray(fieldMessages) ? messages.concat(fieldMessages) : messages;
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
        initTourSelect2() {
            if (!this.$refs.tourSelect || !window.$ || !window.$.fn || !window.$.fn.select2) {
                return;
            }

            const $tourSelect = window.$(this.$refs.tourSelect);
            $tourSelect.select2({
                width: '100%',
                placeholder: 'Select a tour',
                allowClear: true,
            });
            $tourSelect.off('change.select2-vue');
            $tourSelect.on('change.select2-vue', () => {
                const value = $tourSelect.val();
                this.form.tour_id = value ? Number(value) : null;
                this.onTourChanged();
            });
        },
        initTourDateSelect2() {
            if (!this.$refs.tourDateSelect || !window.$ || !window.$.fn || !window.$.fn.select2) {
                return;
            }

            const $tourDateSelect = window.$(this.$refs.tourDateSelect);
            $tourDateSelect.select2({
                width: '100%',
                placeholder: 'Select a tour date',
                allowClear: true,
            });
            $tourDateSelect.off('change.select2-vue');
            $tourDateSelect.on('change.select2-vue', () => {
                const value = $tourDateSelect.val();
                this.form.tour_date_id = value ? Number(value) : null;
            });
        },
        syncTourSelect2Value() {
            if (!this.$refs.tourSelect || !window.$ || !window.$.fn || !window.$.fn.select2) {
                return;
            }

            const value = this.form.tour_id ? String(this.form.tour_id) : null;
            window.$(this.$refs.tourSelect).val(value).trigger('change.select2');
        },
        syncTourDateSelect2Value() {
            if (!this.$refs.tourDateSelect || !window.$ || !window.$.fn || !window.$.fn.select2) {
                return;
            }

            const value = this.form.tour_date_id ? String(this.form.tour_date_id) : null;
            window.$(this.$refs.tourDateSelect).val(value).trigger('change.select2');
        },
        togglePassengerCreator() {
            this.showPassengerCreator = !this.showPassengerCreator;

            if (!this.showPassengerCreator) {
                this.resetQuickPassengerForm();
            }
        },
        resetQuickPassengerForm() {
            this.newPassengerForm = this.getDefaultNewPassengerForm();
            this.newPassengerError = null;
            this.clearNewPassengerValidationErrors();
        },
        async submitQuickPassenger() {
            this.creatingPassenger = true;
            this.newPassengerError = null;
            this.clearNewPassengerValidationErrors();

            try {
                const payload = {
                    first_name: this.newPassengerForm.first_name,
                    last_name: this.newPassengerForm.last_name,
                    email: this.newPassengerForm.email || null,
                    phone: this.newPassengerForm.phone || null,
                    status: 'Enabled',
                };
                const response = await window.axios.post('/api/v1/passengers', payload);
                const newPassenger = response.data && response.data.data ? response.data.data : null;

                if (!newPassenger) {
                    throw new Error('Passenger payload missing');
                }

                this.passengers.unshift(newPassenger);
                this.form.passenger_ids = [Number(newPassenger.id)].concat(this.form.passenger_ids);
                this.form.passenger_ids = Array.from(new Set(this.form.passenger_ids));
                this.passengerSearchInput = '';
                this.resetQuickPassengerForm();
                this.showPassengerCreator = false;
                await Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Passenger created and selected.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            } catch (error) {
                const hasFieldErrors = this.setNewPassengerValidationErrors(error);
                const errorMessage = this.extractApiError(
                    error,
                    'Unable to create passenger. Please review your input and try again.'
                );
                if (!hasFieldErrors) {
                    this.newPassengerError = errorMessage;
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
                this.creatingPassenger = false;
            }
        },
        onTourChanged() {
            if (!this.form.tour_date_id) {
                return;
            }

            const exists = this.availableTourDates.some(
                (tourDate) => Number(tourDate.id) === Number(this.form.tour_date_id)
            );

            if (!exists) {
                this.form.tour_date_id = null;
            }
        },
        async fetchToursForSelection() {
            const perPage = 50;
            let page = 1;
            let lastPage = 1;
            const allItems = [];

            try {
                do {
                    const response = await window.axios.get('/api/v1/tours', {
                        params: {
                            status: 'Public',
                            page,
                            per_page: perPage,
                        },
                    });

                    const payload = response.data || {};
                    const batch = payload.data || [];
                    const meta = payload.meta || {};

                    allItems.push(...batch);
                    lastPage = meta.last_page || 1;
                    page += 1;
                } while (page <= lastPage);

                this.tours = allItems;
                this.$nextTick(() => {
                    this.initTourSelect2();
                    this.syncTourSelect2Value();
                });
            } catch (error) {
                this.formError = this.extractApiError(error, 'Unable to load tours for booking.');
            }
        },
        async fetchPassengersForSelection() {
            this.passengersLoading = true;
            const perPage = 50;
            let page = 1;
            let lastPage = 1;
            const allItems = [];

            try {
                do {
                    const response = await window.axios.get('/api/v1/passengers', {
                        params: {
                            page,
                            per_page: perPage,
                        },
                    });

                    const payload = response.data || {};
                    const batch = payload.data || [];
                    const meta = payload.meta || {};

                    allItems.push(...batch);
                    lastPage = meta.last_page || 1;
                    page += 1;
                } while (page <= lastPage);

                this.passengers = allItems;
            } catch (error) {
                this.formError = this.extractApiError(error, 'Unable to load passengers for booking.');
            } finally {
                this.passengersLoading = false;
            }
        },
        async fetchBookings({ reference = '', status = '', page = 1 } = {}) {
            if (this.isFormRoute) {
                return;
            }

            this.loadingList = true;
            this.listError = null;

            try {
                const response = await window.axios.get('/api/v1/bookings', {
                    params: {
                        reference: reference || undefined,
                        status: status || undefined,
                        page,
                        per_page: this.pagination.per_page,
                    },
                });

                const payload = response.data || {};
                this.items = payload.data || [];
                this.pagination = {
                    current_page: payload.meta ? payload.meta.current_page : 1,
                    last_page: payload.meta ? payload.meta.last_page : 1,
                    per_page: payload.meta ? payload.meta.per_page : 10,
                    total: payload.meta ? payload.meta.total : 0,
                };
            } catch (error) {
                this.listError = this.extractApiError(error, 'Unable to load bookings. Please try again.');
            } finally {
                this.loadingList = false;
            }
        },
        async fetchBookingForEdit(id) {
            this.loadingForm = true;
            this.formError = null;
            this.form = this.getDefaultForm();

            try {
                const response = await window.axios.get('/api/v1/bookings/' + id);
                const item = response.data && response.data.data ? response.data.data : null;

                if (!item) {
                    this.formError = 'Booking not found.';
                    return;
                }

                this.form = {
                    id: item.id,
                    reference: item.reference || '',
                    tour_id: item.tour_id || null,
                    tour_date_id: item.tour_date_id || null,
                    status: item.status || 'Submitted',
                    passenger_ids: Array.isArray(item.passengers) ? item.passengers.map((p) => Number(p.id)) : [],
                };
                this.onTourChanged();
            } catch (error) {
                this.formError = this.extractApiError(error, 'Unable to load this booking.');
            } finally {
                this.loadingForm = false;
            }
        },
        async submitBookingForm() {
            this.submitting = true;
            this.formError = null;
            this.clearValidationErrors();

            try {
                const wasEditing = this.isEditing;
                const payload = {
                    tour_id: this.form.tour_id,
                    tour_date_id: this.form.tour_date_id,
                    passenger_ids: this.form.passenger_ids,
                };

                if (wasEditing) {
                    payload.status = this.form.status;
                    await window.axios.put('/api/v1/bookings/' + this.form.id, payload);
                } else {
                    await window.axios.post('/api/v1/bookings', payload);
                }

                this.form = this.getDefaultForm();
                await Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: wasEditing ? 'Booking updated successfully.' : 'Booking created successfully.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                this.goToIndex();
            } catch (error) {
                const hasFieldErrors = this.setValidationErrors(error);
                const errorMessage = this.extractApiError(
                    error,
                    'Unable to save booking. Please review your selections.'
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
        syncFormFromRoute() {
            if (!this.isFormRoute) {
                this.form = this.getDefaultForm();
                this.formError = null;
                this.validationErrors = {};
                return;
            }

            if (this.$route.name === 'bookings.create') {
                this.form = this.getDefaultForm();
                this.formError = null;
                this.validationErrors = {};
                return;
            }

            const routeId = parseInt(this.$route.params.id, 10);

            if (Number.isNaN(routeId)) {
                this.form = this.getDefaultForm();
                this.formError = 'Invalid booking id.';
                return;
            }

            if (this.form.id === routeId) {
                return;
            }

            this.fetchBookingForEdit(routeId);
        },
        goToCreate() {
            this.$router.push({ name: 'bookings.create', query: this.$route.query });
        },
        goToIndex() {
            this.$router.push({ name: 'bookings.index', query: this.$route.query });
        },
        goToPassengers() {
            this.$router.push({ name: 'passengers.index' });
        },
        goToInvoices() {
            this.$router.push({ name: 'invoices.index' });
        },
        goToInvoiceEdit(invoiceId) {
            this.$router.push({
                name: 'invoices.edit',
                params: { id: String(invoiceId) },
            });
        },
        startEdit(item) {
            this.$router.push({
                name: 'bookings.edit',
                params: { id: String(item.id) },
                query: this.$route.query,
            });
        },
        submitSearch() {
            this.$router.push({
                name: 'bookings.index',
                query: {
                    reference: this.referenceInput || undefined,
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
                name: 'bookings.index',
                query: {
                    reference: this.referenceInput || undefined,
                    status: this.statusFilter || undefined,
                    page,
                },
            });
        },
        formatDateRange(startDate, endDate) {
            if (!endDate) {
                return startDate || '-';
            }

            return startDate + ' to ' + endDate;
        },
    },
    beforeDestroy() {
        if (this.searchDebounceTimer) {
            clearTimeout(this.searchDebounceTimer);
        }

        if (this.$refs.tourSelect && window.$ && window.$.fn && window.$.fn.select2) {
            window.$(this.$refs.tourSelect).off('change.select2-vue');
            window.$(this.$refs.tourSelect).select2('destroy');
        }

        if (this.$refs.tourDateSelect && window.$ && window.$.fn && window.$.fn.select2) {
            window.$(this.$refs.tourDateSelect).off('change.select2-vue');
            window.$(this.$refs.tourDateSelect).select2('destroy');
        }
    },
};
</script>
