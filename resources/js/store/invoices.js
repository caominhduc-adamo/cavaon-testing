const state = {
    items: [],
    loading: false,
    error: null,
    invoiceNumberFilter: '',
    statusFilter: '',
    pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
    },
};

const mutations = {
    setLoading(currentState, isLoading) {
        currentState.loading = isLoading;
    },
    setError(currentState, error) {
        currentState.error = error;
    },
    setInvoiceNumberFilter(currentState, invoiceNumber) {
        currentState.invoiceNumberFilter = invoiceNumber;
    },
    setStatusFilter(currentState, status) {
        currentState.statusFilter = status;
    },
    setInvoices(currentState, payload) {
        currentState.items = payload.data || [];
        currentState.pagination = {
            current_page: payload.meta ? payload.meta.current_page : 1,
            last_page: payload.meta ? payload.meta.last_page : 1,
            per_page: payload.meta ? payload.meta.per_page : 10,
            total: payload.meta ? payload.meta.total : 0,
        };
    },
};

const actions = {
    async fetchInvoices({ commit, state }, { invoiceNumber = '', page = 1, status = '' } = {}) {
        commit('setLoading', true);
        commit('setError', null);
        commit('setInvoiceNumberFilter', invoiceNumber);
        commit('setStatusFilter', status);

        try {
            const response = await window.axios.get('/api/v1/invoices', {
                params: {
                    invoice_number: invoiceNumber || undefined,
                    status: status || undefined,
                    page,
                    per_page: state.pagination.per_page,
                },
            });

            commit('setInvoices', response.data || {});
        } catch (error) {
            const apiMessage = error.response &&
                error.response.data &&
                error.response.data.message
                ? error.response.data.message
                : null;

            commit('setError', apiMessage || 'Unable to load invoices. Please try again.');
        } finally {
            commit('setLoading', false);
        }
    },
    async fetchInvoiceById(_, id) {
        const response = await window.axios.get('/api/v1/invoices/' + id);
        const payload = response.data || {};

        return payload.data || null;
    },
    async updateInvoice(_, { id, payload }) {
        const response = await window.axios.put('/api/v1/invoices/' + id, payload);

        return response.data || {};
    },
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
};
