import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const state = {
    items: [],
    loading: false,
    error: null,
    search: '',
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
    setTours(currentState, payload) {
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
    async fetchTours({ commit, state }, { q = '', page = 1 } = {}) {
        commit('setLoading', true);
        commit('setError', null);
        commit('setSearch', q);

        try {
            const response = await window.axios.get('/api/v1/tours', {
                params: {
                    q: q || undefined,
                    page,
                    per_page: state.pagination.per_page,
                },
            });

            commit('setTours', response.data);
        } catch (error) {
            const apiMessage = error.response &&
                error.response.data &&
                error.response.data.message
                ? error.response.data.message
                : null;

            commit('setError', apiMessage || 'Unable to load tours. Please try again.');
        } finally {
            commit('setLoading', false);
        }
    },
};

export default new Vuex.Store({
    state,
    mutations,
    actions,
});
