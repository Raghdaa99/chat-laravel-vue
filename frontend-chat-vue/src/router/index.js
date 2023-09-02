import {createRouter, createWebHistory} from 'vue-router'
import {userStore} from "@/stores";
import AuthLayout from "../components/AuthLayout.vue";
import Blank from "@/components/Blank.vue";
import MainChat from "@/components/MainChat.vue";
import IndexPage from "@/views/IndexPage.vue";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/',
            name: 'Home',
            component: IndexPage,
            meta: {requiresAuth: true},
            children: [
                {
                    path: '/',
                    name: 'blank',
                    component: Blank,
                },
            ],
        },

        {
            path: '/auth',
            redirect: '/login',
            name: 'Auth',
            component: AuthLayout,
            meta: {requiresGuest: true},
            children: [
                {
                    path: "/login",
                    name: "Login",
                    component:  () => import('../views/Auth/Login.vue'),
                },
                {
                    path: "/register",
                    name: "Register",
                    component: () => import('../views/Auth/Register.vue'),
                },
                {
                    path: '/reset-password',
                    name: 'ResetPassword',
                    component: () => import('../views/Auth/ResetPassword.vue')
                },
            ]
        },
        {
            path: '/:pathMatch(.*)',
            name: 'NotFound',
            component: import('../views/ErrorNotFound.vue'),
        }
    ]
})
router.beforeEach((to, from, next) => {
    const useStore = userStore();
    if ((to.meta.requiresAuth) && !useStore.user.token) { // if user not have token
        next({name: 'Login'})
    } else if (useStore.user.token && (to.meta.requiresGuest)) {
        next({name: 'Home'});
    } else {
        next();
    }
})
export default router
