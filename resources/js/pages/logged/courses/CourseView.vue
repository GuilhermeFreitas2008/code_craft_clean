<!-- CourseView.vue -->
<template>
  <CourseDisplay 
    :course="courseData" 
    :loading="loading" 
  />
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/axios'
import { useUserStore } from '@/stores/userStore'
import { useWatchlistStore } from '@/stores/watchlistStore'
import CourseDisplay from './CourseDisplay.vue'
import type { ApiCourse, UserProgress, UserCourseProgress } from '@/types/course.types'

interface Lesson {
  title: string
  completed: boolean
}

interface Module {
  title: string
  lessons: Lesson[]
}

interface Course {
  id: number
  title: string
  description: string
  progressPercentage: number
  tags: string[]
  lastUpdate: string
  modules: Module[]
  difficulty: string
  category: string
}

const route = useRoute()
const courseId = Number(route.params.id)
const userStore = useUserStore()
const watchlistStore = useWatchlistStore()

const courseData = ref<Course>()
const loading = ref(true)

const formatLastUpdate = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long' 
  })
}

const fetchCourseData = async () => {
  loading.value = true
  
  try {
    if (userStore.isAuthenticated() && watchlistStore.items.length === 0) {
      await watchlistStore.fetchWatchlist()
    }
    
    const courseResponse = await api.get(`/courses/${courseId}`)
    const apiCourse: ApiCourse = courseResponse.data
    
    let completedLessonIds: number[] = []
    let progressPercent = 0
    let hasEnrollment = false

    if (userStore.isAuthenticated()) {
      try {
        const enrollmentResponse = await api.get('/enrollments', {
          params: { course_id: courseId }
        })
        hasEnrollment = enrollmentResponse.data.length > 0
        
        if (hasEnrollment) {
          const progressResponse = await api.get('/user-course-progress', {
            params: { course_id: courseId }
          })
          const courseProgress: UserCourseProgress = progressResponse.data[0]
          progressPercent = courseProgress?.progress_percent || 0
          
          const lessonsProgressResponse = await api.get('/user-progress', {
            params: { course_id: courseId }
          })
          completedLessonIds = lessonsProgressResponse.data.map((p: UserProgress) => p.lesson_id)
        }
      } catch (err) {
        console.warn('Erro ao buscar progresso:', err)
      }
    }

    const modulesWithProgress = apiCourse.modules.map(module => ({
      title: module.title,
      lessons: module.lessons.map(lesson => ({
        title: lesson.title,
        completed: hasEnrollment ? completedLessonIds.includes(lesson.id) : false
      }))
    }))

    courseData.value = {
      id: apiCourse.id,
      title: apiCourse.title,
      description: apiCourse.description,
      progressPercentage: progressPercent,
      tags: apiCourse.topics.map(topic => topic.name),
      lastUpdate: formatLastUpdate(apiCourse.updated_at),
      modules: modulesWithProgress,
      difficulty: apiCourse.difficulty.name,
      category: apiCourse.category.name
    }
    
  } catch (err: any) {
    console.error('Erro ao carregar curso:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCourseData()
})
</script>