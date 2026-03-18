import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/axios'
import type { Module, Lesson, Resource, Comment, CommentWithLikeStatus } from '@/types/lesson.types'
import { useUserStore } from './userStore'

export interface LikeCommentResponse {
    success: boolean
    likes?: number
    liked?: boolean
    error?: string
}

export const useCourseStore = defineStore('course', () => {
    const currentCourseId = ref<number | null>(null)
    const courseTitle = ref<string>('')
    const modules = ref<Module[]>([])
    const currentLessonId = ref<number | null>(null)
    const isLoading = ref(false)
    const error = ref<string | null>(null)
    
    const currentLessonResources = ref<Resource[]>([])
    const currentLessonComments = ref<CommentWithLikeStatus[]>([])
    
    // TRIGGER PARA FORÇAR REATIVIDADE
    const updateTrigger = ref(0)

    // ================================================
    // FUNÇÃO AUXILIAR PARA MAPEAR COMENTÁRIOS COM AVATAR
    // ================================================
    const mapCommentWithAvatar = (comment: any, currentUserId?: number): CommentWithLikeStatus => {
        const userId = comment.userId || comment.user_id || comment.user?.id
        const userName = comment.userName || comment.user?.username || 'Unknown'
        const userAvatar = comment.userAvatar || comment.user?.avatar || null
        
        // 👇 DEBUG: Ver o que está a chegar
        console.log('🎯 Mapeando comentário:', { 
            id: comment.id, 
            userId, 
            userName, 
            userAvatar,
            rawComment: comment 
        })
        
        // Descobrir se é uma resposta e para quem está a responder
        let replyToUserName = null
        if (comment.parent_id) {
            const findParent = (comments: any[], parentId: number): string | null => {
                for (const c of comments) {
                    if (c.id === parentId) {
                        return c.userName || c.user?.username || 'Unknown'
                    }
                    if (c.replies?.length) {
                        const found = findParent(c.replies, parentId)
                        if (found) return found
                    }
                }
                return null
            }
            replyToUserName = findParent([comment], comment.parent_id)
        }

        return {
            id: comment.id,
            userId: userId,
            userName: userName,
            userInitials: userName.charAt(0).toUpperCase(),
            userAvatar: userAvatar,  // 👈 Isto já está correto!
            content: comment.content,
            createdAt: comment.createdAt || comment.created_at,
            likes: comment.likes || 0,
            isLikedByCurrentUser: comment.is_liked_by_user || false,
            replyToUserName: replyToUserName,
            replies: comment.replies?.map((reply: any) => mapCommentWithAvatar(reply, currentUserId))
        }
    }

    // ================================================
    // Computed
    // ================================================
    const currentLesson = computed<Lesson | null>(() => {
        if (!currentLessonId.value || !modules.value.length) return null
        
        void updateTrigger.value
        
        for (const module of modules.value) {
            const lesson = module.lessons.find(l => l.id === currentLessonId.value)
            if (lesson) {
                return {
                    ...lesson,
                    resources: lesson.resources ? [...lesson.resources] : []
                }
            }
        }
        return null
    })

    const totalLessons = computed(() => {
        void updateTrigger.value
        return modules.value.reduce((total, module) => total + module.lessons.length, 0)
    })

    const completedLessons = computed(() => {
        void updateTrigger.value
        return modules.value.reduce(
            (total, module) => total + module.lessons.filter(l => l.completed).length, 
            0
        )
    })

    const courseProgress = computed(() => {
        void updateTrigger.value
        if (totalLessons.value === 0) return 0
        return Math.round((completedLessons.value / totalLessons.value) * 100)
    })

    // ================================================
    // Buscar dados do curso
    // ================================================
    const fetchCourse = async (courseId: number, headers = {}) => {
        const userStore = useUserStore()
        isLoading.value = true
        error.value = null
        
        try {
            const courseResponse = await api.get(`/courses/${courseId}`, { headers })
            const courseData = courseResponse.data
            
            currentCourseId.value = courseId
            courseTitle.value = courseData.title || 'Curso de Teste'
            
            let completedLessonIds: number[] = []
            
            if (userStore.isAuthenticated()) {
                try {
                    const progressResponse = await api.get('/user-progress', {
                        params: { course_id: courseId },
                        headers
                    })
                    completedLessonIds = progressResponse.data
                        .filter((p: any) => p.completed === true)
                        .map((p: any) => p.lesson_id)
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

            updateTrigger.value++
            
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Erro ao carregar curso'
        } finally {
            isLoading.value = false
        }
    }

    // ================================================
    // Buscar recursos de uma lição
    // ================================================
    const fetchLessonResources = async (lessonId: number, headers = {}) => {
        try {
            const response = await api.get(`/lessons/${lessonId}/resources`, { headers })
            currentLessonResources.value = response.data
            return response.data
        } catch (err) {
            currentLessonResources.value = []
            return []
        }
    }

    // ================================================
    // Buscar comentários de uma lição (COM AVATAR)
    // ================================================
    const fetchLessonComments = async (lessonId: number, headers = {}) => {
        try {
            console.log('📥 Buscando comentários da lição:', lessonId)
            const response = await api.get(`/lessons/${lessonId}/comments`, { headers })
            
            console.log('📦 Resposta da API (comentários):', response.data)
            
            const userStore = useUserStore()
            const commentsWithAvatar = response.data.map((comment: any) => 
                mapCommentWithAvatar(comment, userStore.user?.id)
            )
            
            console.log('✅ Comentários mapeados:', commentsWithAvatar)
            
            currentLessonComments.value = commentsWithAvatar
            return commentsWithAvatar
        } catch (err) {
            console.error('❌ Erro ao buscar comentários:', err)
            currentLessonComments.value = []
            return []
        }
    }

    // ================================================
    // Criar comentário (ATUALIZADO)
    // ================================================
    const createComment = async (lessonId: number, content: string, parentId: number | null = null) => {
        try {
            const response = await api.post(`/lessons/${lessonId}/comments`, {
                content,
                parent_id: parentId
            })
            
            const userStore = useUserStore()
            const newComment = mapCommentWithAvatar(response.data, userStore.user?.id)
            
            if (parentId) {
                const parentComment = currentLessonComments.value.find(c => c.id === parentId)
                if (parentComment) {
                    if (!parentComment.replies) parentComment.replies = []
                    parentComment.replies.push(newComment)
                }
            } else {
                currentLessonComments.value.unshift(newComment)
            }
            
            return { success: true, comment: newComment }
        } catch (err: any) {
            return { 
                success: false, 
                error: err.response?.data?.error || 'Erro ao criar comentário'
            }
        }
    }

    // ================================================
    // Editar comentário
    // ================================================
    const editComment = async (commentId: number, content: string) => {
        try {
            const response = await api.put(`/comments/${commentId}`, { content })
            
            const updateCommentContent = (comments: CommentWithLikeStatus[]): boolean => {
                for (const comment of comments) {
                    if (comment.id === commentId) {
                        comment.content = content
                        return true
                    }
                    if (comment.replies && comment.replies.length > 0) {
                        if (updateCommentContent(comment.replies)) return true
                    }
                }
                return false
            }
            
            updateCommentContent(currentLessonComments.value)
            
            return { success: true, comment: response.data }
        } catch (err: any) {
            return { 
                success: false, 
                error: err.response?.data?.error || 'Erro ao editar comentário'
            }
        }
    }

    // ================================================
    // Apagar comentário
    // ================================================
    const deleteComment = async (commentId: number) => {
        try {
            await api.delete(`/comments/${commentId}`)
            
            const removeComment = (comments: CommentWithLikeStatus[]): boolean => {
                for (let i = 0; i < comments.length; i++) {
                    if (comments[i].id === commentId) {
                        comments.splice(i, 1)
                        return true
                    }
                    if (comments[i].replies && comments[i].replies!.length > 0) {
                        if (removeComment(comments[i].replies!)) return true
                    }
                }
                return false
            }
            
            removeComment(currentLessonComments.value)
            
            return { success: true }
        } catch (err: any) {
            return { 
                success: false, 
                error: err.response?.data?.error || 'Erro ao apagar comentário'
            }
        }
    }

    // ================================================
    // Dar like em comentário
    // ================================================
    const likeComment = async (commentId: number): Promise<LikeCommentResponse> => {
        try {
            const response = await api.post(`/comments/${commentId}/like`)
            
            const updateCommentLikes = (comments: CommentWithLikeStatus[]) => {
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
            
            return { 
                success: true, 
                likes: response.data.likes,
                liked: response.data.liked 
            }
        } catch (err: any) {
            return { 
                success: false, 
                error: err.response?.data?.error || 'Erro ao dar like'
            }
        }
    }

    // ================================================
    // Marcar/DESMARCAR lição como completa
    // ================================================
    const markLessonComplete = async (lessonId: number) => {
        try {
            const response = await api.post(`/lessons/${lessonId}/complete`)
            
            const lesson = modules.value
                .flatMap(m => m.lessons)
                .find(l => l.id === lessonId)
            
            if (lesson) {
                lesson.completed = response.data.completed ?? !lesson.completed
            }
            
            updateTrigger.value++
            
            return { success: true, completed: lesson?.completed }
        } catch (err: any) {
            return { 
                success: false, 
                error: err.response?.data?.error || 'Erro ao marcar lição'
            }
        }
    }

    // ================================================
    // NOVAS FUNÇÕES PARA ATUALIZAÇÃO OTIMISTA
    // ================================================
    
    const fetchUpdatedProgress = async (courseId: number) => {
        try {
            const response = await api.get('/user-progress', {
                params: { course_id: courseId }
            })
            
            const completedLessonIds = response.data
                .filter((p: any) => p.completed === true)
                .map((p: any) => p.lesson_id)
            
            return { completedLessonIds }
        } catch (err) {
            console.error('Erro ao buscar progresso:', err)
            return null
        }
    }

    const updateLessonCompletionStatus = (lessonId: number, completed: boolean) => {
        for (const module of modules.value) {
            const lesson = module.lessons.find(l => l.id === lessonId)
            if (lesson) {
                lesson.completed = completed
                break
            }
        }
        updateTrigger.value++
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
        courseTitle.value = ''
        modules.value = []
        currentLessonId.value = null
        currentLessonResources.value = []
        currentLessonComments.value = []
        error.value = null
    }

    return {
        currentCourseId,
        courseTitle,
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
        editComment,      
        deleteComment,    
        likeComment,
        markLessonComplete,
        selectLesson,
        clearCourse,
        updateTrigger,
        fetchUpdatedProgress,
        updateLessonCompletionStatus,
    }
})