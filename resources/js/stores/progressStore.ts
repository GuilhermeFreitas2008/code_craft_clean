import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/axios'
import { useUserStore } from './userStore'

// 👇 DECLARAÇÃO PARA TYPESCRIPT
declare global {
  interface Window {
    __PROGRESS_STORE__: any;
  }
}

export const useProgressStore = defineStore('progress', () => {
    const coursesWithProgress = ref<number[]>([])
    const completedCourses = ref<number[]>([])
    const isLoading = ref(false)

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
            
            // 👉 CONVERSÃO SEGURA: converte para número antes de comparar
            coursesWithProgress.value = response.data
                .filter((p: any) => {
                    const progress = Number(p.progress_percent)
                    return progress > 0 && progress < 100
                })
                .map((p: any) => p.course_id)
            
            completedCourses.value = response.data
                .filter((p: any) => {
                    const progress = Number(p.progress_percent)
                    return progress === 100
                })
                .map((p: any) => p.course_id)
            
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

    // 👇 EXPOR GLOBALMENTE PARA DEBUG (opcional, podes remover se não precisares)
    if (typeof window !== 'undefined') {
        window.__PROGRESS_STORE__ = {
            fetchProgressCourses,
            get completed() { return completedCourses.value },
            get inProgress() { return coursesWithProgress.value },
            isLoading
        };
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