import { createRouter, createWebHistory } from 'vue-router';
import ExamplePage from '@/pages/Example.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: ExamplePage,
        },
    ],
});

export default router;

