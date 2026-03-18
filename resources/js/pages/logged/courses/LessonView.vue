<template>
  <div class="flex min-h-screen flex-col bg-background">
    <!-- Navbar -->
    <NavBar 
      :sidebar-visible="lessonSidebarOpen"
      :user-name="userStore.user?.name"
      :user-email="userStore.user?.email"
      :user-avatar="userStore.user?.avatar"
      :user-initials="userStore.user?.name?.charAt(0)"
      @toggle-mobile-menu="toggleMobileMenu"
      @toggle-sidebar="toggleLessonSidebar"
    />

    <div class="flex flex-1">
      <!-- Lesson Sidebar -->
      <LessonSidebar 
        :modules="courseStore.modules"
        :current-lesson-id="courseStore.currentLessonId || 0"
        :open="isMobile ? mobileMenuOpen : lessonSidebarOpen"
        :is-mobile="isMobile"
        :course-id="courseId"
        :updating-lesson-id="updatingLessonId"
        @close="closeMobileMenu"
        @lesson-select="handleLessonSelect"
      />

      <!-- Main Content -->
      <div 
        class="flex-1 transition-all duration-300 ease-out"
        :class="lessonSidebarOpen && !isMobile ? 'lg:ml-80' : 'lg:ml-0'"
      >
        <main class="p-4 lg:p-8">
          <LessonDisplay
            :key="courseStore.updateTrigger"
            :lesson="courseStore.currentLesson"
            :resources="courseStore.currentLessonResources"
            :comments="mappedComments"
            :loading="courseStore.isLoading"
            :error="courseStore.error"
            :active-section="activeSection"
            :can-remove-completion="canRemoveCompletion"
            :user-avatar="userStore.user?.avatar"
            :user-initials="userInitials"
            :replying-to="replyToComment"
            :comment-submitting="commentSubmitting"
            :new-comment="newComment"
            :is-editing-comment="isEditingComment"
            :editing-comment-id="editingCommentId"
            :is-deleting-comment="isDeletingComment"
            :deleting-comment-id="deletingCommentId"
            :liking-comment-id="likingCommentId"
            :is-updating-completion="isUpdatingCompletion"
            @update:new-comment="newComment = $event"
            @retry="fetchLessonData"
            @video-play="onVideoPlay"
            @video-pause="onVideoPause"
            @video-ended="onVideoEnded"
            @video-loaded="onVideoLoaded"
            @toggle-section="toggleSection"
            @toggle-complete="toggleLessonComplete"
            @submit-comment="submitComment"
            @like-comment="handleLikeComment"
            @reply-to="setReplyTo"
            @cancel-reply="cancelReply"
            @clear-comment="clearComment"
            @delete-comment="handleDeleteComment"
            @edit-comment="handleEditComment"
          />
        </main>
      </div>
    </div>

    <!-- Modal de Conclusão do Curso -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div 
          v-if="showCompletionModal"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
          <!-- Overlay -->
          <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeCompletionModal"></div>
          
          <!-- Modal Content -->
          <div class="relative bg-card border border-primary/20 rounded-2xl max-w-md w-full p-8 shadow-2xl">
            <!-- Ícone de Sucesso -->
            <div class="flex justify-center mb-6">
              <div class="w-20 h-20 rounded-full bg-primary/20 flex items-center justify-center">
                <Trophy :size="40" class="text-primary" />
              </div>
            </div>
            
            <!-- Título -->
            <h2 class="text-2xl font-bold text-center text-foreground mb-2">
              🎉 Congratulations!
            </h2>
            
            <!-- Mensagem -->
            <p class="text-center text-foreground/70 mb-8">
              You've successfully completed the 
              <span class="font-semibold text-primary">"{{ courseStore.courseTitle || 'Curso de Teste' }}"</span> course! 
              Great job on your dedication and hard work.
            </p>
            
            <!-- Estatísticas -->
            <div class="bg-white/5 rounded-xl p-4 mb-8">
              <div class="flex justify-between items-center mb-2">
                <span class="text-foreground/60">Course progress</span>
                <span class="text-primary font-semibold">{{ courseStore.courseProgress }}%</span>
              </div>
              <div class="h-2 w-full rounded-full bg-white/10 mb-4">
                <div 
                  class="h-full rounded-full bg-primary transition-all duration-500"
                  :style="{ width: `${courseStore.courseProgress}%` }"
                ></div>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-foreground/60">{{ courseStore.completedLessons }} lessons completed</span>
                <span class="text-foreground/60">🏆 Certificate earned</span>
              </div>
            </div>
            
            <!-- Botões de Ação -->
            <div class="flex flex-col sm:flex-row gap-3">
              <button
                @click="continueLearning"
                class="flex-1 px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-primary/90 transition-colors"
              >
                Continue Learning
              </button>
              <button
                @click="goToDashboard"
                class="flex-1 px-6 py-3 bg-white/5 text-foreground rounded-lg font-medium hover:bg-white/10 transition-colors border border-white/10"
              >
                Go to Dashboard
              </button>
            </div>
            
            <!-- Fechar (X) -->
            <button 
              @click="closeCompletionModal"
              class="absolute top-4 right-4 text-foreground/40 hover:text-foreground transition-colors"
            >
              <X :size="20" />
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { CheckCircle, XCircle, Trophy, X } from 'lucide-vue-next'
import NavBar from '@/components/layout/NavBar.vue'
import LessonSidebar from '@/components/lessons/LessonSidebar.vue'
import LessonDisplay from './LessonDisplay.vue'
import { useUserStore } from '@/stores/userStore'
import { useCourseStore, type LikeCommentResponse } from '@/stores/courseStore'
import { useProgressStore } from '@/stores/progressStore'
import type { Comment, CommentWithLikeStatus } from '@/types/lesson.types'

const props = defineProps<{
  id: string
  lessonId: string
}>()

const router = useRouter()
const userStore = useUserStore()
const courseStore = useCourseStore()
const progressStore = useProgressStore()

const courseId = Number(props.id)
const initialLessonId = Number(props.lessonId)

// State
const lessonSidebarOpen = ref(true)
const mobileMenuOpen = ref(false)
const isMobile = ref(window.innerWidth < 1024)
const activeSection = ref<'resources' | 'comments' | null>(null)
const canRemoveCompletion = ref(true)
const removeDelayTimer = ref<ReturnType<typeof setTimeout> | null>(null)
const showSuccessMessage = ref(false)
const showCompletionModal = ref(false)
const newComment = ref('')
const commentSubmitting = ref(false)
const replyToComment = ref<CommentWithLikeStatus | null>(null)

// Estados para loading de edição e delete
const isEditingComment = ref(false)
const editingCommentId = ref<number | null>(null)
const isDeletingComment = ref(false)
const deletingCommentId = ref<number | null>(null)

// Estado para animação de like
const likingCommentId = ref<number | null>(null)

// Estados para loading do complete
const isUpdatingCompletion = ref(false)
const updatingLessonId = ref<number | null>(null)

// ================================================
// FUNÇÃO PARA SCROLL PARA O TOPO
// ================================================
const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  })
}

// ================================================
// COMPUTED
// ================================================
const userInitials = computed(() => {
  return userStore.user?.name?.charAt(0) || 'U'
})

// Mapear os comentários da API para o formato esperado
const mappedComments = computed<CommentWithLikeStatus[]>(() => {
  const rawComments = courseStore.currentLessonComments || []
  
  const mapComment = (comment: any): CommentWithLikeStatus => {
    const userId = comment.userId || comment.user_id || comment.user?.id
    const userName = comment.userName || comment.user?.username || 'Unknown'
    const userInitials = comment.userInitials || 
                        comment.user?.username?.charAt(0).toUpperCase() || 
                        userName.charAt(0).toUpperCase()
    
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
      replyToUserName = findParent(rawComments, comment.parent_id)
    }
    
    const mappedReplies = comment.replies?.map((reply: any) => mapComment(reply))

    return {
      id: comment.id,
      userId: userId,
      userName: userName,
      userInitials: userInitials,
      content: comment.content,
      createdAt: comment.createdAt || comment.created_at,
      likes: comment.likes || 0,
      isLikedByCurrentUser: comment.is_liked_by_user || false,
      replyToUserName: replyToUserName,
      replies: mappedReplies
    }
  }

  return rawComments.map(mapComment)
})

// ================================================
// VIDEO EVENTS
// ================================================
const onVideoPlay = () => {}
const onVideoPause = () => {}
const onVideoEnded = () => {}
const onVideoLoaded = () => {}

// ================================================
// SECTION TOGGLE
// ================================================
const toggleSection = (section: 'resources' | 'comments') => {
  if (activeSection.value === section) {
    activeSection.value = null
  } else {
    activeSection.value = section
  }
}

// ================================================
// COMMENTS
// ================================================
const setReplyTo = (comment: CommentWithLikeStatus) => {
  replyToComment.value = comment
  activeSection.value = 'comments'
}

const cancelReply = () => {
  replyToComment.value = null
  newComment.value = ''
}

const clearComment = () => {
  newComment.value = ''
  replyToComment.value = null
}

const submitComment = async (content: string) => {
  if (!content?.trim() || !courseStore.currentLessonId) return
  
  commentSubmitting.value = true
  
  const parentId = replyToComment.value?.id ?? null
  
  const result = await courseStore.createComment(
    courseStore.currentLessonId,
    content,
    parentId
  )
  
  if (result?.success) {
    newComment.value = ''
    replyToComment.value = null
    await courseStore.fetchLessonComments(courseStore.currentLessonId)
  }
  
  commentSubmitting.value = false
}

// ================================================
// LIKE COMMENT
// ================================================
const handleLikeComment = async (commentId: number) => {
  if (!commentId || !courseStore.currentLessonId) return
  
  likingCommentId.value = commentId
  
  const findComment = (comments: any[], id: number): any => {
    for (const comment of comments) {
      if (comment.id === id) return comment
      if (comment.replies?.length) {
        const found = findComment(comment.replies, id)
        if (found) return found
      }
    }
    return null
  }
  
  const currentComment = findComment(courseStore.currentLessonComments, commentId)
  
  if (!currentComment) {
    likingCommentId.value = null
    return
  }
  
  const previousLikes = currentComment.likes
  const previousLikedState = currentComment.is_liked_by_user || false
  
  if (previousLikedState) {
    currentComment.likes = previousLikes - 1
    currentComment.is_liked_by_user = false
  } else {
    currentComment.likes = previousLikes + 1
    currentComment.is_liked_by_user = true
  }
  
  try {
    const result: LikeCommentResponse = await courseStore.likeComment(commentId)
    
    if (!result?.success) {
      currentComment.likes = previousLikes
      currentComment.is_liked_by_user = previousLikedState
    } else {
      currentComment.likes = result.likes
      if (result.liked !== undefined) {
        currentComment.is_liked_by_user = result.liked
      }
    }
  } catch (error) {
    currentComment.likes = previousLikes
    currentComment.is_liked_by_user = previousLikedState
    console.error('Erro ao dar like:', error)
  } finally {
    setTimeout(() => {
      likingCommentId.value = null
    }, 300)
  }
}

// ================================================
// EDIT COMMENT
// ================================================
const handleEditComment = async (commentId: number, content: string) => {
  if (!commentId || !content?.trim() || !courseStore.currentLessonId) return
  
  isEditingComment.value = true
  editingCommentId.value = commentId
  
  const result = await courseStore.editComment(commentId, content)
  
  if (result?.success) {
    await courseStore.fetchLessonComments(courseStore.currentLessonId)
  }
  
  isEditingComment.value = false
  editingCommentId.value = null
}

// ================================================
// DELETE COMMENT
// ================================================
const handleDeleteComment = async (commentId: number) => {
  if (!commentId || !courseStore.currentLessonId) return
  
  isDeletingComment.value = true
  deletingCommentId.value = commentId
  
  const result = await courseStore.deleteComment(commentId)
  
  if (result?.success) {
    await courseStore.fetchLessonComments(courseStore.currentLessonId)
  }
  
  isDeletingComment.value = false
  deletingCommentId.value = null
}

// ================================================
// LESSON COMPLETION - OTIMIZADO E CORRIGIDO
// ================================================
const startRemoveDelay = () => {
  canRemoveCompletion.value = false
  
  if (removeDelayTimer.value) {
    clearTimeout(removeDelayTimer.value)
  }
  
  removeDelayTimer.value = setTimeout(() => {
    canRemoveCompletion.value = true
    removeDelayTimer.value = null
  }, 2000)
}

const toggleLessonComplete = async () => {
  if (!courseStore.currentLesson) return
  
  const wasCompleted = courseStore.currentLesson.completed
  const lessonId = courseStore.currentLesson.id
  
  if (wasCompleted && !canRemoveCompletion.value) {
    return
  }
  
  // Ativar loading
  isUpdatingCompletion.value = true
  updatingLessonId.value = lessonId
  
  // Optimistic update - atualiza a UI imediatamente
  const lesson = courseStore.modules
    .flatMap(m => m.lessons)
    .find(l => l.id === lessonId)
  
  if (lesson) {
    lesson.completed = !wasCompleted
    // Forçar atualização do trigger
    courseStore.updateTrigger++
  }
  
  // Fazer a chamada API
  const result = await courseStore.markLessonComplete(lessonId)
  
  if (result?.success) {
    if (!wasCompleted) {
      startRemoveDelay()
      showSuccessMessage.value = true
      setTimeout(() => {
        showSuccessMessage.value = false
      }, 3000)
    } else {
      canRemoveCompletion.value = true
    }
    
    // Verificar se o curso foi completo
    const completedCount = courseStore.completedLessons
    const totalCount = courseStore.totalLessons
    
    if (completedCount === totalCount && totalCount > 0 && !wasCompleted) {
      showCompletionModal.value = true
    }
    
    // 👉 CORREÇÃO CRÍTICA: Atualizar o progressStore
    await progressStore.fetchProgressCourses()
    
    // Atualizar o progresso sem recarregar tudo
    try {
      const progressData = await courseStore.fetchUpdatedProgress(courseId)
      if (progressData) {
        // Atualizar todas as lições com o novo progresso
        progressData.completedLessonIds.forEach((id: number) => {
          courseStore.updateLessonCompletionStatus(id, true)
        })
      }
    } catch (error) {
      console.error('Erro ao atualizar progresso:', error)
    }
  } else {
    // Reverter em caso de erro
    if (lesson) {
      lesson.completed = wasCompleted
      courseStore.updateTrigger++
    }
  }
  
  // Desativar loading
  setTimeout(() => {
    isUpdatingCompletion.value = false
    updatingLessonId.value = null
  }, 500)
}

// ================================================
// COURSE COMPLETION MODAL
// ================================================
watch(() => courseStore.completedLessons, (newValue, oldValue) => {
  if (newValue === courseStore.totalLessons && courseStore.totalLessons > 0 && newValue > (oldValue || 0)) {
    showCompletionModal.value = true
  }
})

const closeCompletionModal = () => {
  showCompletionModal.value = false
}

const continueLearning = () => {
  closeCompletionModal()
  // Pode redirecionar para a próxima lição ou lista de cursos
}

const goToDashboard = () => {
  router.push('/user')
  closeCompletionModal()
}

// ================================================
// SIDEBAR CONTROLS
// ================================================
const toggleLessonSidebar = () => {
  lessonSidebarOpen.value = !lessonSidebarOpen.value
}

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

// ================================================
// HANDLE LESSON SELECT
// ================================================
const handleLessonSelect = async (lessonId: number) => {
  activeSection.value = null
  courseStore.selectLesson(lessonId)
  
  await Promise.all([
    courseStore.fetchLessonResources(lessonId),
    courseStore.fetchLessonComments(lessonId)
  ])
  
  router.push(`/course/${courseId}/lesson/${lessonId}`)
  
  if (isMobile.value) {
    closeMobileMenu()
  }
}

// ================================================
// FETCH DATA
// ================================================
const fetchLessonData = async () => {
  if (courseId) {
    await courseStore.fetchCourse(courseId)
    
    if (courseStore.modules?.length > 0) {
      const lessonId = initialLessonId || courseStore.modules[0]?.lessons[0]?.id
      
      if (lessonId) {
        courseStore.selectLesson(lessonId)
        
        await Promise.all([
          courseStore.fetchLessonResources(lessonId),
          courseStore.fetchLessonComments(lessonId)
        ])
      }
    }
  }
}

// ================================================
// WATCH PARA MUDANÇA DE LIÇÃO - SCROLL PARA O TOPO
// ================================================
watch(() => courseStore.currentLessonId, () => {
  setTimeout(() => {
    scrollToTop()
  }, 100)
})

// ================================================
// WINDOW RESIZE
// ================================================
const handleResize = () => {
  isMobile.value = window.innerWidth < 1024
  if (!isMobile.value) {
    mobileMenuOpen.value = false
  }
}

// ================================================
// LIFECYCLE
// ================================================
onMounted(() => {
  window.addEventListener('resize', handleResize)
  handleResize()
  fetchLessonData()
  scrollToTop()
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  if (removeDelayTimer.value) {
    clearTimeout(removeDelayTimer.value)
  }
  courseStore.clearCourse()
})
</script>