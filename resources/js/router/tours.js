import Vue from 'vue';
import Router from 'vue-router';
import ToursPage from '../pages/ToursPage.vue';
import BookingsPage from '../pages/BookingsPage.vue';
import PassengersPage from '../pages/PassengersPage.vue';

Vue.use(Router);

export default new Router({
    mode: 'history',
    routes: [
        {
            path: '/tours',
            name: 'tours.index',
            component: ToursPage,
        },
        {
            path: '/tours/create',
            name: 'tours.create',
            component: ToursPage,
        },
        {
            path: '/tours/:id/edit',
            name: 'tours.edit',
            component: ToursPage,
        },
        {
            path: '/bookings',
            name: 'bookings.index',
            component: BookingsPage,
        },
        {
            path: '/bookings/create',
            name: 'bookings.create',
            component: BookingsPage,
        },
        {
            path: '/bookings/:id/edit',
            name: 'bookings.edit',
            component: BookingsPage,
        },
        {
            path: '/passengers',
            name: 'passengers.index',
            component: PassengersPage,
        },
        {
            path: '/passengers/create',
            name: 'passengers.create',
            component: PassengersPage,
        },
        {
            path: '/passengers/:id/edit',
            name: 'passengers.edit',
            component: PassengersPage,
        },
    ],
});
