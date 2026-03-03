<template>
    <div class="container">
        <module-page-header
            title="Passengers Management"
            :show-create-button="!isFormRoute"
            @create="goToCreate"
        />

        <div v-if="isFormRoute" class="card mb-4">
            <div class="card-header">
                {{ isEditing ? 'Edit passenger #' + form.id : 'Create new passenger' }}
            </div>
            <div class="card-body">
                <div v-if="loadingForm" class="alert alert-info">Loading passenger details...</div>
                <div v-if="formError" class="alert alert-danger">{{ formError }}</div>

                <form v-if="!loadingForm" @submit.prevent="submitPassengerForm">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="first-name">First name</label>
                            <input
                                id="first-name"
                                v-model.trim="form.first_name"
                                type="text"
                                :class="['form-control', { 'is-invalid': getFieldError('first_name') }]"
                                required
                            >
                            <div v-if="getFieldError('first_name')" class="invalid-feedback d-block">
                                {{ getFieldError('first_name') }}
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="last-name">Last name</label>
                            <input
                                id="last-name"
                                v-model.trim="form.last_name"
                                type="text"
                                :class="['form-control', { 'is-invalid': getFieldError('last_name') }]"
                                required
                            >
                            <div v-if="getFieldError('last_name')" class="invalid-feedback d-block">
                                {{ getFieldError('last_name') }}
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="email">Email</label>
                            <input
                                id="email"
                                v-model.trim="form.email"
                                type="email"
                                :class="['form-control', { 'is-invalid': getFieldError('email') }]"
                            >
                            <div v-if="getFieldError('email')" class="invalid-feedback d-block">
                                {{ getFieldError('email') }}
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Phone</label>
                            <input
                                id="phone"
                                v-model.trim="form.phone"
                                type="text"
                                :class="['form-control', { 'is-invalid': getFieldError('phone') }]"
                            >
                            <div v-if="getFieldError('phone')" class="invalid-feedback d-block">
                                {{ getFieldError('phone') }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select
                            id="status"
                            v-model="form.status"
                            :class="['form-control', { 'is-invalid': getFieldError('status') }]"
                        >
                            <option value="Enabled">Enabled</option>
                            <option value="Disabled">Disabled</option>
                        </select>
                        <div v-if="getFieldError('status')" class="invalid-feedback d-block">
                            {{ getFieldError('status') }}
                        </div>
                    </div>

                    <div class="d-flex">
                        <button type="submit" class="btn btn-success mr-2" :disabled="submitting">
                            {{ submitting ? 'Saving...' : (isEditing ? 'Update passenger' : 'Create passenger') }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary" :disabled="submitting" @click="goToIndex">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <template v-if="!isFormRoute">
            <module-filter-bar
                :search-value="searchInput"
                search-placeholder="Search by name/email/phone"
                :status-value="statusFilter"
                :status-options="statusOptions"
                @update:searchValue="searchInput = $event.trim()"
                @update:statusValue="statusFilter = $event"
                @change="submitSearch"
            />

            <div v-if="loadingList" class="alert alert-info mb-3">Loading passengers...</div>
            <div v-else-if="listError" class="alert alert-danger mb-3">{{ listError }}</div>
            <div v-else-if="!items.length" class="alert alert-secondary mb-3">No passengers found.</div>

            <div v-else class="table-responsive mb-3">
                <table class="table table-striped table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.id">
                            <td>{{ item.id }}</td>
                            <td>{{ item.first_name }} {{ item.last_name }}</td>
                            <td>{{ item.email || '-' }}</td>
                            <td>{{ item.phone || '-' }}</td>
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

            <module-pagination
                :show="hasPagination"
                aria-label="Passengers pagination"
                :is-first-page="isFirstPage"
                :is-last-page="isLastPage"
                :loading="loadingList"
                :current-page="currentPage"
                :last-page="lastPage"
                @go-to-page="goToPage"
            />
        </template>
    </div>
</template>

<script>
import Swal from 'sweetalert2';
import ModulePageHeader from '../components/modules/ModulePageHeader.vue';
import ModuleFilterBar from '../components/modules/ModuleFilterBar.vue';
import ModulePagination from '../components/modules/ModulePagination.vue';

export default {
    name: 'PassengersPage',
    components: {
        ModulePageHeader,
        ModuleFilterBar,
        ModulePagination,
    },
    data() {
        return {
            loadingForm: false,
            submitting: false,
            formError: null,
            validationErrors: {},
            searchInput: '',
            statusFilter: '',
            searchDebounceTimer: null,
            form: this.getDefaultForm(),
        };
    },
    computed: {
        isFormRoute() {
            return this.$route.name === 'passengers.create' || this.$route.name === 'passengers.edit';
        },
        isEditing() {
            return !!this.form.id;
        },
        items() {
            return this.$store.state.passengers.items;
        },
        loadingList() {
            return this.$store.state.passengers.loading;
        },
        listError() {
            return this.$store.state.passengers.error;
        },
        currentPage() {
            return this.$store.state.passengers.pagination.current_page;
        },
        lastPage() {
            return this.$store.state.passengers.pagination.last_page;
        },
        hasPagination() {
            return this.$store.state.passengers.pagination.total > this.$store.state.passengers.pagination.per_page;
        },
        isFirstPage() {
            return this.currentPage <= 1;
        },
        isLastPage() {
            return this.currentPage >= this.lastPage;
        },
        statusOptions() {
            return [
                { value: 'Enabled', label: 'Enabled' },
                { value: 'Disabled', label: 'Disabled' },
            ];
        },
    },
    watch: {
        '$route.query': {
            immediate: true,
            handler(query) {
                const q = typeof query.q === 'string' ? query.q : '';
                const status = query.status === 'Enabled' || query.status === 'Disabled' ? query.status : '';
                const parsedPage = parseInt(query.page, 10);
                const page = Number.isNaN(parsedPage) || parsedPage < 1 ? 1 : parsedPage;

                this.searchInput = q;
                this.statusFilter = status;
                this.fetchPassengers({ q, status, page });
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
            }, 800);
        },
    },
    methods: {
        getDefaultForm() {
            return {
                id: null,
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
                status: 'Enabled',
                updated_at: null,
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
        async fetchPassengers({ q = '', status = '', page = 1 } = {}) {
            if (this.isFormRoute) {
                return;
            }

            await this.$store.dispatch('passengers/fetchPassengers', {
                q,
                status,
                page,
            });
        },
        async fetchPassengerForEdit(id) {
            this.loadingForm = true;
            this.formError = null;
            this.form = this.getDefaultForm();

            try {
                const item = await this.$store.dispatch('passengers/fetchPassengerById', id);

                if (!item) {
                    this.formError = 'Passenger not found.';
                    return;
                }

                this.form = {
                    id: item.id,
                    first_name: item.first_name || '',
                    last_name: item.last_name || '',
                    email: item.email || '',
                    phone: item.phone || '',
                    status: item.status || 'Enabled',
                    updated_at: item.updated_at || null,
                };
            } catch (error) {
                this.formError = this.extractApiError(error, 'Unable to load this passenger.');
            } finally {
                this.loadingForm = false;
            }
        },
        async submitPassengerForm() {
            this.submitting = true;
            this.formError = null;
            this.clearValidationErrors();

            try {
                const wasEditing = this.isEditing;
                const payload = {
                    first_name: this.form.first_name,
                    last_name: this.form.last_name,
                    email: this.form.email || null,
                    phone: this.form.phone || null,
                    status: this.form.status,
                };

                if (wasEditing) {
                    payload.updated_at = this.form.updated_at;
                    await this.$store.dispatch('passengers/updatePassenger', { id: this.form.id, payload });
                } else {
                    await this.$store.dispatch('passengers/createPassenger', payload);
                }

                this.goToIndex();
                await Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: wasEditing ? 'Passenger updated successfully.' : 'Passenger created successfully.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            } catch (error) {
                const hasFieldErrors = this.setValidationErrors(error);
                const errorMessage = this.extractApiError(error, 'Unable to save passenger.');
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

            if (this.$route.name === 'passengers.create') {
                this.form = this.getDefaultForm();
                this.formError = null;
                this.validationErrors = {};
                return;
            }

            const routeId = parseInt(this.$route.params.id, 10);

            if (Number.isNaN(routeId)) {
                this.form = this.getDefaultForm();
                this.formError = 'Invalid passenger id.';
                return;
            }

            if (this.form.id === routeId) {
                return;
            }

            this.fetchPassengerForEdit(routeId);
        },
        goToCreate() {
            this.$router.push({ name: 'passengers.create', query: this.$route.query });
        },
        goToIndex() {
            this.$router.push({ name: 'passengers.index', query: this.$route.query });
        },
        goToBookings() {
            this.$router.push({ name: 'bookings.index' });
        },
        goToInvoices() {
            this.$router.push({ name: 'invoices.index' });
        },
        startEdit(item) {
            this.$router.push({
                name: 'passengers.edit',
                params: { id: String(item.id) },
                query: this.$route.query,
            });
        },
        submitSearch() {
            this.$router.push({
                name: 'passengers.index',
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
                name: 'passengers.index',
                query: {
                    q: this.searchInput || undefined,
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
