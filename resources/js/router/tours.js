import Vue from 'vue';
import Router from 'vue-router';
import ToursPage from '../pages/ToursPage.vue';

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
    ],
});
