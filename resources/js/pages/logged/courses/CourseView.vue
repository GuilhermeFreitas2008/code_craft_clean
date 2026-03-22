<template>
  <CourseDisplay 
    :course="courseData ?? undefined" 
    :loading="courseStore.isLoading" 
  />
</template>

<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useUserStore } from '@/stores/userStore'
import { useWatchlistStore } from '@/stores/watchlistStore'
import { useCourseStore } from '@/stores/courseStore'
import { useUiStore } from '@/stores/uiStore'
import CourseDisplay from './CourseDisplay.vue'

const route = useRoute()
const courseId = Number(route.params.id)
const userStore = useUserStore()
const watchlistStore = useWatchlistStore()
const courseStore = useCourseStore()
const uiStore = useUiStore()

const courseData = computed(() => {
  if (!courseStore.currentCourseId || !courseStore.modules.length) return undefined
  
  const totalLessonsVal = courseStore.totalLessons
  const completedLessonsVal = courseStore.completedLessons
  const progressPercentage = totalLessonsVal > 0 ? (completedLessonsVal / totalLessonsVal) * 100 : 0
  
  return {
    id: courseStore.currentCourseId,
    title: courseStore.courseTitle,
    description: 'Curso de programação',
    progressPercentage: progressPercentage,
    isEnrolled: courseStore.isEnrolled,
    tags: ['programação', 'web', 'javascript'],
    lastUpdate: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long' }),
    modules: courseStore.modules as any,
    difficulty: 'intermediate',
    category: 'programação'
  }
})

onMounted(async () => {
  console.log('🔍 CourseView mounted, courseId:', courseId)
  
  uiStore.setActiveMenu(null)
  
  if (userStore.isAuthenticated() && watchlistStore.items.length === 0) {
    await watchlistStore.fetchWatchlist()
  }
  
  if (courseId) {
    await courseStore.fetchCourse(courseId)
  }
})

watch(() => courseStore.updateTrigger, () => {
  console.log('🔄 updateTrigger mudou, curso atualizado')
})
</script>