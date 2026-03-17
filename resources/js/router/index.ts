import { createRouter, createWebHistory } from 'vue-router';
import LandingPage from '@/pages/public/LandingPage.vue';
import LoginPage from '@/pages/public/LoginPage.vue';
import RegisterPage from '@/pages/public/RegisterPage.vue';
import UserPage from '@/pages/logged/UserPage.vue';
import CourseView from '@/pages/logged/courses/CourseView.vue';
import LessonView from '@/pages/logged/courses/LessonView.vue';
import ProfileView from '@/pages/logged/profile/ProfileView.vue';
import SettingsView from '@/pages/logged/profile/SettingsView.vue';


const router = createRouter({
    history: createWebHistory(),
    routes: [
        {path: '/', name: 'landing', component: LandingPage},
        
        {path: '/login', name: 'login', component: LoginPage},

        {path: '/register', name: 'register', component: RegisterPage},

        {path: '/user', name: 'user', component: UserPage},

        {path: '/course/:id', name: 'course', component: CourseView, props: true},

        { path: '/course/:id/lesson/:lessonId', name: 'lesson', component: LessonView, props: true },

        { path: '/profile', name: 'profile', component: ProfileView, meta: { requiresAuth: true } },
        
        { path: '/settings', name: 'settings', component: SettingsView, meta: { requiresAuth: true } },
    ],
});

export default router;

