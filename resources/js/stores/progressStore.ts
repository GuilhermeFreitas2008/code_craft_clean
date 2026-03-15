import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/axios'
import { useUserStore } from './userStore'

export const useProgressStore = defineStore('progress', () => {
    const coursesWithProgress = ref<number[]>([]) // IDs dos cursos que têm progresso (>0%)
    const completedCourses = ref<number[]>([]) // IDs dos cursos completos (100%)
    const isLoading = ref(false)

    // ================================================
    // Buscar cursos com progresso do utilizador
    // ================================================
    const fetchProgressCourses = async () => {
        const userStore = useUserStore()
        if (!userStore.isAuthenticated()) {
            coursesWithProgress.value = []
            completedCourses.value = []
            return
        }

        isLoading.value = true
        try {
            const response = await api.get('/user-course-progress')
            
            // Separar por tipo de progresso
            coursesWithProgress.value = response.data
                .filter((p: any) => p.progress_percent > 0 && p.progress_percent < 100)
                .map((p: any) => p.course_id)
            
            completedCourses.value = response.data
                .filter((p: any) => p.progress_percent === 100)
                .map((p: any) => p.course_id)
            
            console.log('📦 Cursos em progresso:', coursesWithProgress.value)
            console.log('📦 Cursos completos:', completedCourses.value)
        } catch (error) {
            console.error('Erro ao carregar progresso:', error)
        } finally {
            isLoading.value = false
        }
    }

    // ================================================
    // Verificar se um curso tem progresso (não completo)
    // ================================================
    const hasProgress = (courseId: number): boolean => {
        return coursesWithProgress.value.includes(courseId)
    }

    // ================================================
    // Verificar se um curso está completo
    // ================================================
    const isCompleted = (courseId: number): boolean => {
        return completedCourses.value.includes(courseId)
    }

    // ================================================
    // Limpar store
    // ================================================
    const clearProgress = () => {
        coursesWithProgress.value = []
        completedCourses.value = []
    }

    return {
        coursesWithProgress,
        completedCourses,
        isLoading,
        fetchProgressCourses,
        hasProgress,
        isCompleted,
        clearProgress
    }
})