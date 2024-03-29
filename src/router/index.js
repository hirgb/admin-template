import Vue from 'vue';
import Router from 'vue-router';

Vue.use(Router);

export default new Router({
    routes: [
        {
            path: '/',
            redirect: '/login'
        },
        {
            path: '/login',
            component: resolve => require(['@/components/Login.vue'], resolve),
        },
        {
            path: '/',
            component: resolve => require(['@/components/Main.vue'], resolve),
            children: [
                {
                    path: '/home',
                    component: resolve => require(['@/components/Home.vue'], resolve),
                },
            ]
        },
        {
            path: '*',
            redirect: '/404'
        },
    ],
})
