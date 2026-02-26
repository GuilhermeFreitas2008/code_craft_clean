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
import CourseDisplay from './CourseDisplay.vue'
import type { ApiCourse, UserProgress, UserCourseProgress, CourseDisplayProps } from '@/types/course.types'

// ================================================
// PROPS & ROUTER
// ================================================
const route = useRoute()
const courseId = Number(route.params.id)
const userStore = useUserStore()

// ================================================
// STATE
// ================================================
const courseData = ref<CourseDisplayProps>()  // 👈 mudado de null para undefined
const loading = ref(true)

// ================================================
// FUNÇÃO PARA FORMATAR DATA
// ================================================
const formatLastUpdate = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long' 
  })
}

// ================================================
// FUNÇÃO PRINCIPAL
// ================================================
const fetchCourseData = async () => {
  loading.value = true
  
  try {
    // 1️⃣ Buscar dados do curso
    const courseResponse = await api.get(`/courses/${courseId}`)
    const apiCourse: ApiCourse = courseResponse.data
    
    // Arrays para progresso
    let completedLessonIds: number[] = []
    let progressPercent = 0
    let hasEnrollment = false

    // 2️⃣ Verificar progresso (só se estiver logado)
    if (userStore.isAuthenticated()) {
      try {
        // Verificar enrollment
        const enrollmentResponse = await api.get('/enrollments', {
          params: { course_id: courseId }
        })
        hasEnrollment = enrollmentResponse.data.length > 0
        
        if (hasEnrollment) {
          // Buscar progresso total
          const progressResponse = await api.get('/user-course-progress', {
            params: { course_id: courseId }
          })
          const courseProgress: UserCourseProgress = progressResponse.data[0]
          progressPercent = courseProgress?.progress_percent || 0
          
          // Buscar lições completadas
          const lessonsProgressResponse = await api.get('/user-progress', {
            params: { course_id: courseId }
          })
          completedLessonIds = lessonsProgressResponse.data.map((p: UserProgress) => p.lesson_id)
        }
      } catch (err) {
        console.warn('Erro ao buscar progresso:', err)
      }
    }

    // 3️⃣ Mapear módulos com progresso
    const modulesWithProgress = apiCourse.modules.map(module => ({
      title: module.title,
      lessons: module.lessons.map(lesson => ({
        title: lesson.title,
        completed: hasEnrollment ? completedLessonIds.includes(lesson.id) : false
      }))
    }))

    // 4️⃣ Construir objeto final
    courseData.value = {
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