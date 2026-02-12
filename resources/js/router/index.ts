import { createRouter, createWebHistory } from 'vue-router';
import LandingPage from '@/pages/public/LandingPage.vue';
import LoginPage from '@/pages/public/LoginPage.vue';
import RegisterPage from '@/pages/public/RegisterPage.vue';


const router = createRouter({
    history: createWebHistory(),
    routes: [
        {path: '/', name: 'landing', component: LandingPage},
        
        {path: '/login', name: 'login', component: LoginPage},

        {path: '/register', name: 'register', component: RegisterPage},
    ],
});

export default router;

