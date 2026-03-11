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
  id: number
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

console.log('🔍 CourseView mounted, courseId:', courseId)

const formatLastUpdate = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long' 
  })
}

const fetchCourseData = async () => {
  console.log('1️⃣ fetchCourseData iniciado')
  loading.value = true
  
  try {
    console.log('2️⃣ courseId:', courseId)
    
    if (userStore.isAuthenticated() && watchlistStore.items.length === 0) {
      console.log('3️⃣ fetching watchlist...')
      await watchlistStore.fetchWatchlist()
    }
    
    console.log('4️⃣ fetching course from API...')
    const courseResponse = await api.get(`/courses/${courseId}`)
    console.log('5️⃣ API response:', courseResponse.data)
    
    const apiCourse: ApiCourse = courseResponse.data
    
    let completedLessonIds: number[] = []
    let progressPercent = 0
    let hasEnrollment = false

    if (userStore.isAuthenticated()) {
      console.log('6️⃣ user is authenticated, fetching progress...')
      try {
        const enrollmentResponse = await api.get('/enrollments', {
          params: { course_id: courseId }
        })
        hasEnrollment = enrollmentResponse.data.length > 0
        console.log('7️⃣ hasEnrollment:', hasEnrollment)
        
        if (hasEnrollment) {
          const progressResponse = await api.get('/user-course-progress', {
            params: { course_id: courseId }
          })
          const courseProgress: UserCourseProgress = progressResponse.data[0]
          progressPercent = courseProgress?.progress_percent || 0
          console.log('8️⃣ progressPercent:', progressPercent)
          
          const lessonsProgressResponse = await api.get('/user-progress', {
            params: { course_id: courseId }
          })
          completedLessonIds = lessonsProgressResponse.data.map((p: UserProgress) => p.lesson_id)
          console.log('9️⃣ completedLessonIds:', completedLessonIds)
        }
      } catch (err) {
        console.warn('⚠️ Erro ao buscar progresso:', err)
      }
    }

    const modulesWithProgress = apiCourse.modules.map(module => {
      console.log('🔸 module:', module.title, 'lessons:', module.lessons.length)
      return {
        title: module.title,
        lessons: module.lessons.map(lesson => ({
          id: lesson.id,
          title: lesson.title,
          completed: hasEnrollment ? completedLessonIds.includes(lesson.id) : false
        }))
      }
    })

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
    
    console.log('🔟 courseData final:', courseData.value)
    
  } catch (err: any) {
    console.error('❌ Erro ao carregar curso:', err)
    console.error('❌ Status:', err.response?.status)
    console.error('❌ Data:', err.response?.data)
  } finally {
    loading.value = false
    console.log('1️⃣1️⃣ loading:', loading.value)
  }
}

onMounted(() => {
  fetchCourseData()
})
</script>