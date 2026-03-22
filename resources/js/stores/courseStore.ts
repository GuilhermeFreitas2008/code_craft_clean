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
    const isEnrolled = ref<boolean>(false) // NOVO: estado de inscrição
    
    const currentLessonResources = ref<Resource[]>([])
    const currentLessonComments = ref<CommentWithLikeStatus[]>([])
    
    // TRIGGER PARA FORÇAR REATIVIDADE
    const updateTrigger = ref(0)

    // ================================================
    // FUNÇÃO AUXILIAR PARA MAPEAR COMENTÁRIOS COM AVATAR
    // ================================================
    const mapCommentWithAvatar = (comment: any, allComments: any[], currentUserId?: number): CommentWithLikeStatus => {
        const userId = comment.userId || comment.user_id || comment.user?.id
        const userName = comment.userName || comment.user?.username || 'Unknown'
        const userAvatar = comment.userAvatar || comment.user?.avatar_url || comment.user?.avatar || null
        
        let replyToUserName = null
        if (comment.parent_id) {
            const parentComment = allComments.find((c: any) => c.id === comment.parent_id)
            if (parentComment) {
                replyToUserName = parentComment.userName || parentComment.user?.username || 'Unknown'
            }
        }

        return {
            id: comment.id,
            userId: userId,
            userName: userName,
            userInitials: userName.charAt(0).toUpperCase(),
            userAvatar: userAvatar,
            content: comment.content,
            createdAt: comment.createdAt || comment.created_at,
            likes: comment.likes || 0,
            isLikedByCurrentUser: comment.is_liked_by_user || false,
            replyToUserName: replyToUserName,
            parent_id: comment.parent_id,
            replies: comment.replies?.map((reply: any) => 
                mapCommentWithAvatar(reply, allComments, currentUserId)
            )
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
            
            // Verificar se está inscrito
            if (userStore.isAuthenticated()) {
                try {
                    const enrollmentResponse = await api.get('/enrollments', {
                        params: { course_id: courseId },
                        headers
                    })
                    isEnrolled.value = enrollmentResponse.data.length > 0
                    console.log('📝 isEnrolled:', isEnrolled.value)
                } catch (err) {
                    console.warn('Erro ao buscar enrollment:', err)
                    isEnrolled.value = false
                }
                
                // Buscar progresso se estiver inscrito
                if (isEnrolled.value) {
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
            } else {
                isEnrolled.value = false
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
                    completed: isEnrolled.value ? completedLessonIds.includes(lesson.id) : false,
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
// Buscar comentários de uma lição
// ================================================
const fetchLessonComments = async (lessonId: number, headers = {}) => {
    try {
        console.log('📥 Buscando comentários da lição:', lessonId)
        const response = await api.get(`/lessons/${lessonId}/comments`, { headers })
        
        console.log('📦 Resposta da API (comentários):', response.data)
        
        const userStore = useUserStore()
        const allComments = response.data
        
        const commentsWithAvatar: CommentWithLikeStatus[] = allComments.map((comment: any) => 
            mapCommentWithAvatar(comment, allComments, userStore.user?.id)
        )
        
        const commentMap = new Map<number, CommentWithLikeStatus>()
        const topLevelComments: CommentWithLikeStatus[] = []
        
        commentsWithAvatar.forEach((comment: CommentWithLikeStatus) => {
            commentMap.set(comment.id, comment)
        })
        
        commentsWithAvatar.forEach((comment: CommentWithLikeStatus) => {
            if (comment.parent_id) {
                const parent = commentMap.get(comment.parent_id)
                if (parent) {
                    if (!parent.replies) parent.replies = []
                    parent.replies.push(comment)
                }
            } else {
                topLevelComments.push(comment)
            }
        })
        
        console.log('✅ Comentários organizados:', topLevelComments)
        
        currentLessonComments.value = topLevelComments
        return topLevelComments
    } catch (err) {
        console.error('❌ Erro ao buscar comentários:', err)
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
            
            const userStore = useUserStore()
            const allComments = currentLessonComments.value.flatMap(c => [c, ...(c.replies || [])])
            
            const newComment = mapCommentWithAvatar(
                response.data, 
                allComments, 
                userStore.user?.id
            )
            
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
        isEnrolled.value = false
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
        isEnrolled,
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