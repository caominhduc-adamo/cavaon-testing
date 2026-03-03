<template>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Invoices Management</h1>
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
                    class="btn btn-outline-primary"
                    @click="goToPassengers"
                >
                    Passengers
                </button>
            </div>
        </div>

        <div v-if="isEditRoute" class="card mb-4">
            <div class="card-header">Edit invoice #{{ form.id }}</div>
            <div class="card-body">
                <div v-if="loadingForm" class="alert alert-info">Loading invoice details...</div>
                <div v-if="formError" class="alert alert-danger">{{ formError }}</div>

                <form v-if="!loadingForm" @submit.prevent="submitInvoiceForm">
                    <div class="form-group">
                        <label>Invoice number</label>
                        <input class="form-control" type="text" :value="form.invoice_number || '-'" disabled>
                    </div>
                    <div class="form-group">
                        <label>Booking reference</label>
                        <input class="form-control" type="text" :value="form.booking_reference || '-'" disabled>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="invoice-amount">Amount</label>
                            <input
                                id="invoice-amount"
                                v-model.number="form.amount"
                                type="number"
                                min="0"
                                step="0.01"
                                :class="['form-control', { 'is-invalid': getFieldError('amount') }]"
                            >
                            <div v-if="getFieldError('amount')" class="invalid-feedback d-block">
                                {{ getFieldError('amount') }}
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="invoice-currency">Currency</label>
                            <input
                                id="invoice-currency"
                                v-model.trim="form.currency"
                                type="text"
                                maxlength="3"
                                :class="['form-control', { 'is-invalid': getFieldError('currency') }]"
                            >
                            <div v-if="getFieldError('currency')" class="invalid-feedback d-block">
                                {{ getFieldError('currency') }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="invoice-status">Status</label>
                        <select
                            id="invoice-status"
                            v-model="form.status"
                            :class="['form-control', { 'is-invalid': getFieldError('status') }]"
                        >
                            <option value="Unpaid">Unpaid</option>
                            <option value="Paid">Paid</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <div v-if="getFieldError('status')" class="invalid-feedback d-block">
                            {{ getFieldError('status') }}
                        </div>
                    </div>

                    <div class="d-flex">
                        <button type="submit" class="btn btn-success mr-2" :disabled="submitting">
                            {{ submitting ? 'Saving...' : 'Update invoice' }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary" :disabled="submitting" @click="goToIndex">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <template v-if="!isEditRoute">
            <div class="form-inline mb-3">
                <input
                    v-model.trim="invoiceNumberInput"
                    type="text"
                    class="form-control mr-2"
                    placeholder="Search by invoice number"
                >
                <select v-model="statusFilter" class="form-control mr-2" @change="submitSearch">
                    <option value="">All statuses</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Paid">Paid</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>

            <div v-if="loadingList" class="alert alert-info mb-3">Loading invoices...</div>
            <div v-else-if="listError" class="alert alert-danger mb-3">{{ listError }}</div>
            <div v-else-if="!items.length" class="alert alert-secondary mb-3">No invoices found.</div>

            <div v-else class="table-responsive mb-3">
                <table class="table table-striped table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>Invoice #</th>
                            <th>Booking</th>
                            <th style="width: 120px;">Amount</th>
                            <th style="width: 100px;">Currency</th>
                            <th style="width: 110px;">Status</th>
                            <th style="width: 90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.id">
                            <td>{{ item.id }}</td>
                            <td>{{ item.invoice_number }}</td>
                            <td>
                                <template v-if="item.booking">
                                    <a
                                        href="#"
                                        @click.prevent="goToBookingEdit(item.booking.id)"
                                    >
                                        {{ item.booking.reference }}
                                    </a>
                                </template>
                                <span v-else>-</span>
                            </td>
                            <td>{{ item.amount }}</td>
                            <td>{{ item.currency }}</td>
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

            <nav v-if="hasPagination" aria-label="Invoices pagination">
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
    name: 'InvoicesPage',
    data() {
        return {
            items: [],
            loadingList: false,
            listError: null,
            loadingForm: false,
            submitting: false,
            formError: null,
            validationErrors: {},
            invoiceNumberInput: '',
            statusFilter: '',
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
        isEditRoute() {
            return this.$route.name === 'invoices.edit';
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
    },
    watch: {
        '$route.query': {
            immediate: true,
            handler(query) {
                const invoiceNumber = typeof query.invoice_number === 'string' ? query.invoice_number : '';
                const status = ['Unpaid', 'Paid', 'Cancelled'].includes(query.status) ? query.status : '';
                const parsedPage = parseInt(query.page, 10);
                const page = Number.isNaN(parsedPage) || parsedPage < 1 ? 1 : parsedPage;

                this.invoiceNumberInput = invoiceNumber;
                this.statusFilter = status;
                this.fetchInvoices({ invoiceNumber, status, page });
            },
        },
        '$route': {
            immediate: true,
            handler() {
                this.syncFormFromRoute();
            },
        },
        invoiceNumberInput(newValue) {
            if (this.isEditRoute) {
                return;
            }

            const routeQuery = typeof this.$route.query.invoice_number === 'string'
                ? this.$route.query.invoice_number
                : '';

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
    },
    methods: {
        getDefaultForm() {
            return {
                id: null,
                invoice_number: '',
                booking_reference: '',
                amount: 0,
                currency: 'USD',
                status: 'Unpaid',
            };
        },
        getFieldError(field) {
            const errors = this.validationErrors[field];
            return Array.isArray(errors) && errors.length ? errors[0] : null;
        },
        clearValidationErrors() {
            this.validationErrors = {};
        },
        setValidationErrors(error) {
            if (error && error.response && error.response.data && error.response.data.errors) {
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
        async fetchInvoices({ invoiceNumber = '', status = '', page = 1 } = {}) {
            if (this.isEditRoute) {
                return;
            }

            this.loadingList = true;
            this.listError = null;

            try {
                const response = await window.axios.get('/api/v1/invoices', {
                    params: {
                        invoice_number: invoiceNumber || undefined,
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
                this.listError = this.extractApiError(error, 'Unable to load invoices. Please try again.');
            } finally {
                this.loadingList = false;
            }
        },
        async fetchInvoiceForEdit(id) {
            this.loadingForm = true;
            this.formError = null;
            this.form = this.getDefaultForm();

            try {
                const response = await window.axios.get('/api/v1/invoices/' + id);
                const item = response.data && response.data.data ? response.data.data : null;

                if (!item) {
                    this.formError = 'Invoice not found.';
                    return;
                }

                this.form = {
                    id: item.id,
                    invoice_number: item.invoice_number || '',
                    booking_reference: item.booking ? item.booking.reference : '',
                    amount: Number(item.amount || 0),
                    currency: item.currency || 'USD',
                    status: item.status || 'Unpaid',
                };
            } catch (error) {
                this.formError = this.extractApiError(error, 'Unable to load this invoice.');
            } finally {
                this.loadingForm = false;
            }
        },
        async submitInvoiceForm() {
            this.submitting = true;
            this.formError = null;
            this.clearValidationErrors();

            try {
                await window.axios.put('/api/v1/invoices/' + this.form.id, {
                    amount: this.form.amount,
                    currency: (this.form.currency || '').toUpperCase(),
                    status: this.form.status,
                });
                await Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Invoice updated successfully.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                this.goToIndex();
            } catch (error) {
                const hasFieldErrors = this.setValidationErrors(error);
                const errorMessage = this.extractApiError(error, 'Unable to save invoice.');
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
            if (!this.isEditRoute) {
                this.form = this.getDefaultForm();
                this.formError = null;
                this.validationErrors = {};
                return;
            }

            const routeId = parseInt(this.$route.params.id, 10);
            if (Number.isNaN(routeId)) {
                this.formError = 'Invalid invoice id.';
                return;
            }

            if (this.form.id === routeId) {
                return;
            }

            this.fetchInvoiceForEdit(routeId);
        },
        startEdit(item) {
            this.$router.push({
                name: 'invoices.edit',
                params: { id: String(item.id) },
                query: this.$route.query,
            });
        },
        goToIndex() {
            this.$router.push({ name: 'invoices.index', query: this.$route.query });
        },
        goToBookings() {
            this.$router.push({ name: 'bookings.index' });
        },
        goToBookingEdit(bookingId) {
            this.$router.push({
                name: 'bookings.edit',
                params: { id: String(bookingId) },
            });
        },
        goToPassengers() {
            this.$router.push({ name: 'passengers.index' });
        },
        submitSearch() {
            this.$router.push({
                name: 'invoices.index',
                query: {
                    invoice_number: this.invoiceNumberInput || undefined,
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
                name: 'invoices.index',
                query: {
                    invoice_number: this.invoiceNumberInput || undefined,
                    status: this.statusFilter || undefined,
                    page,
                },
            });
        },
    },
    beforeDestroy() {
        if (this.searchDebounceTimer) {
            clearTimeout(this.searchDebounceTimer);
        }
    },
};
</script>
