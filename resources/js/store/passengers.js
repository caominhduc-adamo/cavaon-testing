const state = {
    items: [],
    loading: false,
    error: null,
    search: '',
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
    setSearch(currentState, search) {
        currentState.search = search;
    },
    setStatusFilter(currentState, status) {
        currentState.statusFilter = status;
    },
    setPassengers(currentState, payload) {
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
    async fetchPassengers({ commit, state }, { q = '', page = 1, status = '' } = {}) {
        commit('setLoading', true);
        commit('setError', null);
        commit('setSearch', q);
        commit('setStatusFilter', status);

        try {
            const response = await window.axios.get('/api/v1/passengers', {
                params: {
                    q: q || undefined,
                    status: status || undefined,
                    page,
                    per_page: state.pagination.per_page,
                },
            });

            commit('setPassengers', response.data || {});
        } catch (error) {
            const apiMessage = error.response &&
                error.response.data &&
                error.response.data.message
                ? error.response.data.message
                : null;

            commit('setError', apiMessage || 'Unable to load passengers. Please try again.');
        } finally {
            commit('setLoading', false);
        }
    },
    async fetchPassengerById(_, id) {
        const response = await window.axios.get('/api/v1/passengers/' + id);
        const payload = response.data || {};

        return payload.data || null;
    },
    async fetchPassengersForSelection(_, { perPage = 50 } = {}) {
        let page = 1;
        let lastPage = 1;
        const allItems = [];

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

        return allItems;
    },
    async createPassenger(_, payload) {
        const response = await window.axios.post('/api/v1/passengers', payload);
        const responsePayload = response.data || {};

        return responsePayload.data || null;
    },
    async updatePassenger(_, { id, payload }) {
        const response = await window.axios.put('/api/v1/passengers/' + id, payload);

        return response.data || {};
    },
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
};
