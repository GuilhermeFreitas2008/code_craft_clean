<!-- CourseView.vue -->
<template>
  <CourseDisplay :course="mockCourse" />
</template>

<script setup lang="ts">
import CourseDisplay from '@/pages/logged/courses/CourseDisplay.vue'

// ================================================
// MOCK DATA FOR DEMONSTRATION - WITH MODULES
// ================================================
const mockCourse = {
  title: 'Web Development with Laravel and Vue.js',
  description: 'Learn to build complete web applications using Laravel on the backend and Vue.js on the frontend. Master the main technologies in the market.',
  progressPercentage: 0,
  tags: ['Laravel', 'PHP', 'API', 'Vue 3', 'Tailwind CSS'],
  lastUpdate: 'February 2025',
  difficulty: 'intermediate',
  category: 'Full Stack',
  modules: [
    {
      title: 'Introduction to Laravel',
      lessons: [
        { title: 'What is Laravel?', completed: false },
        { title: 'Installing Laravel', completed: false },
        { title: 'Project Structure', completed: false }
      ]
    },
    {
      title: 'Routing & Controllers',
      lessons: [
        { title: 'Basic Routing', completed: false },
        { title: 'Route Parameters', completed: false },
        { title: 'Creating Controllers', completed: false },
        { title: 'Resource Controllers', completed: false }
      ]
    },
    {
      title: 'Blade Templating',
      lessons: [
        { title: 'Blade Syntax', completed: false },
        { title: 'Layouts & Components', completed: false },
        { title: 'Forms & CSRF', completed: false }
      ]
    },
    {
      title: 'Database & Eloquent',
      lessons: [
        { title: 'Migrations', completed: false },
        { title: 'Models & Relationships', completed: false },
        { title: 'Query Builder', completed: false },
        { title: 'Eloquent ORM', completed: false }
      ]
    }
  ]
}
</script>