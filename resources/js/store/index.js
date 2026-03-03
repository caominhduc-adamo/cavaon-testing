import Vue from 'vue';
import Vuex from 'vuex';
import tours from './tours';
import bookings from './bookings';
import passengers from './passengers';
import invoices from './invoices';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        tours,
        bookings,
        passengers,
        invoices,
    },
});
