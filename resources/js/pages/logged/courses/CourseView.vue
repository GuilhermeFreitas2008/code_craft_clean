<!-- CourseView.vue -->
<template>
  <CourseDisplay :course="mockCourse" />
</template>

<script setup lang="ts">
import CourseDisplay from '@/pages/logged/courses/CourseDisplay.vue'

// ================================================
// MOCK DATA FOR DEMONSTRATION - PROGRESS 0%
// ================================================
const mockCourse = {
  title: 'Web Development with Laravel and Vue.js',
  description: 'Learn to build complete web applications using Laravel on the backend and Vue.js on the frontend. Master the main technologies in the market.',
  progressPercentage: 0, // 👈 ALTERADO PARA 0%
  tags: ['Laravel', 'PHP', 'API', 'Vue 3', 'Tailwind CSS'],
  lastUpdate: 'February 2025',
  lessons: [
    { title: 'Introduction to Laravel', completed: false }, // 👈 TODOS COMEÇAM FALSE
    { title: 'Environment Setup', completed: false },
    { title: 'Routes and Controllers', completed: false },
    { title: 'Blade Templates', completed: false },
    { title: 'Eloquent ORM - Part 1', completed: false },
    { title: 'Eloquent ORM - Part 2', completed: false },
    { title: 'Authentication with Laravel', completed: false },
    { title: 'Introduction to Vue.js', completed: false },
    { title: 'Vue Components', completed: false },
    { title: 'API Communication', completed: false }
  ]
}
</script>