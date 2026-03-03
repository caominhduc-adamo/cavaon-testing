const state = {
    items: [],
    loading: false,
    error: null,
    referenceFilter: '',
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
    setReferenceFilter(currentState, reference) {
        currentState.referenceFilter = reference;
    },
    setStatusFilter(currentState, status) {
        currentState.statusFilter = status;
    },
    setBookings(currentState, payload) {
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
    async fetchBookings({ commit, state }, { reference = '', page = 1, status = '' } = {}) {
        commit('setLoading', true);
        commit('setError', null);
        commit('setReferenceFilter', reference);
        commit('setStatusFilter', status);

        try {
            const response = await window.axios.get('/api/v1/bookings', {
                params: {
                    reference: reference || undefined,
                    status: status || undefined,
                    page,
                    per_page: state.pagination.per_page,
                },
            });

            commit('setBookings', response.data || {});
        } catch (error) {
            const apiMessage = error.response &&
                error.response.data &&
                error.response.data.message
                ? error.response.data.message
                : null;

            commit('setError', apiMessage || 'Unable to load bookings. Please try again.');
        } finally {
            commit('setLoading', false);
        }
    },
    async fetchBookingById(_, id) {
        const response = await window.axios.get('/api/v1/bookings/' + id);
        const payload = response.data || {};

        return payload.data || null;
    },
    async createBooking(_, payload) {
        const response = await window.axios.post('/api/v1/bookings', payload);

        return response.data || {};
    },
    async updateBooking(_, { id, payload }) {
        const response = await window.axios.put('/api/v1/bookings/' + id, payload);

        return response.data || {};
    },
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
};
