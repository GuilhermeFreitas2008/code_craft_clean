// stores/courseStore.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/axios'
import type { Module, Lesson, Resource, Comment } from '@/types/lesson.types'
import { useUserStore } from './userStore'

export const useCourseStore = defineStore('course', () => {
    const currentCourseId = ref<number | null>(null)
    const modules = ref<Module[]>([])
    const currentLessonId = ref<number | null>(null)
    const isLoading = ref(false)
    const error = ref<string | null>(null)
    
    const currentLessonResources = ref<Resource[]>([])
    const currentLessonComments = ref<Comment[]>([])

    // ================================================
    // Computed
    // ================================================
    const currentLesson = computed<Lesson | null>(() => {
        if (!currentLessonId.value || !modules.value.length) return null
        
        for (const module of modules.value) {
            const lesson = module.lessons.find(l => l.id === currentLessonId.value)
            if (lesson) return lesson
        }
        return null
    })

    const totalLessons = computed(() => {
        return modules.value.reduce((total, module) => total + module.lessons.length, 0)
    })

    const completedLessons = computed(() => {
        return modules.value.reduce(
            (total, module) => total + module.lessons.filter(l => l.completed).length, 
            0
        )
    })

    const courseProgress = computed(() => {
        if (totalLessons.value === 0) return 0
        return Math.round((completedLessons.value / totalLessons.value) * 100)
    })

    // ================================================
    // Buscar dados do curso
    // ================================================
    const fetchCourse = async (courseId: number) => {
        const userStore = useUserStore()
        isLoading.value = true
        error.value = null
        
        try {
            const courseResponse = await api.get(`/courses/${courseId}`)
            const courseData = courseResponse.data
            
            currentCourseId.value = courseId
            
            let completedLessonIds: number[] = []
            
            if (userStore.isAuthenticated()) {
                try {
                    const progressResponse = await api.get('/user-progress', {
                        params: { course_id: courseId }
                    })
                    completedLessonIds = progressResponse.data.map((p: any) => p.lesson_id)
                } catch (err) {
                    console.warn('Erro ao buscar progresso:', err)
                }
            }
            
            modules.value = courseData.modules.map((module: any) => ({
                id: module.id,
                title: module.title,
                position: module.position,
                lessons: module.lessons.map((lesson: any) => ({
                    id: lesson.id,
                    title: lesson.title,
                    content: lesson.content,
                    video_url: lesson.video_url,
                    position: lesson.position,
                    completed: completedLessonIds.includes(lesson.id),
                    resources: []
                }))
            }))
            
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Erro ao carregar curso'
        } finally {
            isLoading.value = false
        }
    }

    // ================================================
    // Buscar recursos de uma lição
    // ================================================
    const fetchLessonResources = async (lessonId: number) => {
        try {
            const response = await api.get(`/lessons/${lessonId}/resources`)
            currentLessonResources.value = response.data
            return response.data
        } catch (err) {
            currentLessonResources.value = []
            return []
        }
    }

    // ================================================
    // Buscar comentários de uma lição
    // ================================================
    const fetchLessonComments = async (lessonId: number) => {
        try {
            const response = await api.get(`/lessons/${lessonId}/comments`)
            currentLessonComments.value = response.data
            return response.data
        } catch (err) {
            currentLessonComments.value = []
            return []
        }
    }

    // ================================================
    // Criar comentário
    // ================================================
    const createComment = async (lessonId: number, content: string, parentId: number | null = null) => {
        try {
            const response = await api.post(`/lessons/${lessonId}/comments`, {
                content,
                parent_id: parentId
            })
            
            if (parentId) {
                const parentComment = currentLessonComments.value.find(c => c.id === parentId)
                if (parentComment) {
                    if (!parentComment.replies) parentComment.replies = []
                    parentComment.replies.push(response.data)
                }
            } else {
                currentLessonComments.value.unshift(response.data)
            }
            
            return { success: true, comment: response.data }
        } catch (err: any) {
            return { 
                success: false, 
                error: err.response?.data?.error || 'Erro ao criar comentário'
            }
        }
    }

    // ================================================
    // Dar like em comentário
    // ================================================
    const likeComment = async (commentId: number) => {
        try {
            const response = await api.post(`/comments/${commentId}/like`)
            
            const updateCommentLikes = (comments: Comment[]) => {
                for (const comment of comments) {
                    if (comment.id === commentId) {
                        comment.likes = response.data.likes
                        return true
                    }
                    if (comment.replies) {
                        if (updateCommentLikes(comment.replies)) return true
                    }
                }
                return false
            }
            
            updateCommentLikes(currentLessonComments.value)
            
            return { success: true, likes: response.data.likes }
        } catch (err: any) {
            return { 
                success: false, 
                error: err.response?.data?.error || 'Erro ao dar like'
            }
        }
    }

    // ================================================
    // Marcar lição como completa
    // ================================================
    const markLessonComplete = async (lessonId: number) => {
        try {
            await api.post(`/lessons/${lessonId}/complete`)
            
            const lesson = modules.value
                .flatMap(m => m.lessons)
                .find(l => l.id === lessonId)
            
            if (lesson) {
                lesson.completed = true
            }
            
            return { success: true }
        } catch (err: any) {
            return { 
                success: false, 
                error: err.response?.data?.error || 'Erro ao marcar lição'
            }
        }
    }

    // ================================================
    // Selecionar lição
    // ================================================
    const selectLesson = (lessonId: number) => {
        currentLessonId.value = lessonId
    }

    // ================================================
    // Limpar store
    // ================================================
    const clearCourse = () => {
        currentCourseId.value = null
        modules.value = []
        currentLessonId.value = null
        currentLessonResources.value = []
        currentLessonComments.value = []
        error.value = null
    }

    return {
        currentCourseId,
        modules,
        currentLessonId,
        currentLesson,
        currentLessonResources,
        currentLessonComments,
        totalLessons,
        completedLessons,
        courseProgress,
        isLoading,
        error,
        fetchCourse,
        fetchLessonResources,
        fetchLessonComments,
        createComment,
        likeComment,
        markLessonComplete,
        selectLesson,
        clearCourse
    }
})