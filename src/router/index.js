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
                {
                    path: '/activity',
                    component: resolve => require(['@/components/Activity/Activity.vue'], resolve),
                },
                {
                    path: '/hotnews',
                    component: resolve => require(['@/components/HotNews/HotNews.vue'], resolve),
                },
                {
                    path: '/mission',
                    component: resolve => require(['@/components/Mission/Mission.vue'], resolve),
                },
                {
                    path: '/deploylist',
                    component: resolve => require(['@/components/Term/DeployList.vue'], resolve),
                },
                {
                    path: '/term',
                    component: resolve => require(['@/components/Term/Term.vue'], resolve),
                    props: (route) => {
                        return {
                            missionId: route.query.missionId
                        }
                    }
                },
                {
                    path: '/plan',
                    component: resolve => require(['@/components/Plan/Plan.vue'], resolve),
                    props: (route) => {
                        return {
                            actName: route.query.actName,
                            actType: route.query.actType
                        }
                    },
                },
                {
                    path: '/activityitem',
                    component: resolve => require(['@/components/ActivityItem/Item.vue'], resolve),
                    props: (route) => {
                        return {
                            actName: route.query.actName,
                            actType: route.query.actType
                        }
                    },
                },
                {
                    path: '/developer',
                    component: resolve => require(['@/components/Developer/Developer.vue'], resolve),
                },
                {
                    path:'/redisintervene',
                    component: resolve => require(['@/components/RedisIntervene/Redis.vue'], resolve),
                },
                {
                    path: '/tools-contentmanager',
                    component: resolve => require(['@/components/Developer/ToolContentManager.vue'], resolve),
                    props: (route) => {
                        return {
                            uid: route.query.uid,
                        }
                    },
                },
                {
                    path: '/tools-genformjson',
                    component: resolve => require(['@/components/Developer/ToolGenFormJson.vue'], resolve),
                },
                {
                    path: '/tools/esmanager',
                    component: resolve => require(['@/components/Tools/EsManager/Index.vue'], resolve),
                },
                {
                    path: '/404',
                    component: resolve => require(['@/components/404.vue'], resolve),
                    meta: { title: '404' }
                },
            ]
        },
        {
            path: '*',
            redirect: '/404'
        },
    ],
})
