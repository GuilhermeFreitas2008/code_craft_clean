<template>
  <CourseDisplay 
    :course="courseData" 
    :loading="isLoading" 
  />
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
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

// Estado local para controlar o loading inicial
const isLoading = ref(true)

const courseData = computed(() => {
  // Enquanto estiver a carregar, retorna undefined para mostrar skeleton
  if (isLoading.value) {
    return undefined
  }
  
  // Se não tem dados, retorna undefined
  if (!courseStore.currentCourseId || !courseStore.modules.length) {
    return undefined
  }
  
  const totalLessonsVal = courseStore.totalLessons
  const completedLessonsVal = courseStore.completedLessons
  const progressPercentage = totalLessonsVal > 0 ? (completedLessonsVal / totalLessonsVal) * 100 : 0
  
  return {
    id: courseStore.currentCourseId,
    title: courseStore.courseTitle,
    description: courseStore.courseDescription || 'Sem descrição',
    progressPercentage: progressPercentage,
    isEnrolled: courseStore.isEnrolled,
    tags: courseStore.courseTopics.length > 0 ? courseStore.courseTopics : ['programação', 'web'],
    lastUpdate: courseStore.courseLastUpdate,
    modules: courseStore.modules as any,
    difficulty: courseStore.courseDifficulty || 'intermediate',
    category: courseStore.courseCategory || 'programação'
  }
})

onMounted(async () => {
  console.log('🔍 CourseView mounted, courseId:', courseId)
  
  // Garantir que loading começa como true IMEDIATAMENTE
  isLoading.value = true
  uiStore.setActiveMenu(null)
  
  if (userStore.isAuthenticated() && watchlistStore.items.length === 0) {
    await watchlistStore.fetchWatchlist()
  }
  
  if (courseId) {
    await courseStore.fetchCourse(courseId)
  }
  
  // Só depois de carregar é que desativamos o loading
  isLoading.value = false
})
</script>