<!-- pages/logged/courses/LessonView.vue -->
<template>
  <div class="flex min-h-screen flex-col bg-background">
    <!-- Navbar -->
    <NavBar 
      :sidebar-visible="lessonSidebarOpen"
      :user-name="userStore.user?.name"
      :user-email="userStore.user?.email"
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
            :lesson="courseStore.currentLesson"
            :resources="courseStore.currentLessonResources"
            :comments="mappedComments"
            :loading="courseStore.isLoading"
            :error="courseStore.error"
            :active-section="activeSection"
            :can-remove-completion="canRemoveCompletion"
            :user-initials="userInitials"
            :replying-to="replyToComment"
            :comment-submitting="commentSubmitting"
            :new-comment="newComment"
            :is-editing-comment="isEditingComment"
            :editing-comment-id="editingCommentId"
            :is-deleting-comment="isDeletingComment"
            :deleting-comment-id="deletingCommentId"
            :liking-comment-id="likingCommentId"
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
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import NavBar from '@/components/layout/NavBar.vue'
import LessonSidebar from '@/components/lessons/LessonSidebar.vue'
import LessonDisplay from './LessonDisplay.vue'
import { useUserStore } from '@/stores/userStore'
import { useCourseStore, type LikeCommentResponse } from '@/stores/courseStore'
import type { Comment, CommentWithLikeStatus } from '@/types/lesson.types'

const props = defineProps<{
  id: string
  lessonId: string
}>()

const router = useRouter()
const userStore = useUserStore()
const courseStore = useCourseStore()

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
  newComment.value = `@${comment.userName} `
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
// LIKE COMMENT - VERSÃO PROFISSIONAL
// ================================================
const handleLikeComment = async (commentId: number) => {
  if (!commentId || !courseStore.currentLessonId) return
  
  // Ativar animação
  likingCommentId.value = commentId
  
  // Encontrar o comentário nos dados REAIS
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
  
  // Guardar estado anterior para possível reversão
  const previousLikes = currentComment.likes
  const previousLikedState = currentComment.is_liked_by_user || false
  
  // ATUALIZAÇÃO OTIMISTA DIRETA
  if (previousLikedState) {
    currentComment.likes = previousLikes - 1
    currentComment.is_liked_by_user = false
  } else {
    currentComment.likes = previousLikes + 1
    currentComment.is_liked_by_user = true
  }
  
  try {
    // Chamar API
    const result: LikeCommentResponse = await courseStore.likeComment(commentId)
    
    if (!result?.success) {
      // Reverter em caso de erro
      currentComment.likes = previousLikes
      currentComment.is_liked_by_user = previousLikedState
    } else {
      // Atualizar com o valor EXATO da API
      currentComment.likes = result.likes
      if (result.liked !== undefined) {
        currentComment.is_liked_by_user = result.liked
      }
    }
  } catch (error) {
    // Reverter em caso de erro inesperado
    currentComment.likes = previousLikes
    currentComment.is_liked_by_user = previousLikedState
    console.error('Erro ao dar like:', error)
  } finally {
    // Remover animação
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
// LESSON COMPLETION
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
  
  if (wasCompleted && !canRemoveCompletion.value) {
    return
  }
  
  const result = await courseStore.markLessonComplete(courseStore.currentLesson.id)
  
  if (result?.success) {
    if (!wasCompleted) {
      startRemoveDelay()
      showSuccessMessage.value = true
      setTimeout(() => {
        showSuccessMessage.value = false
      }, 3000)
    } else {
      canRemoveCompletion.value = true
      if (courseStore.currentCourseId) {
        await courseStore.fetchCourse(courseStore.currentCourseId)
      }
    }
  }
}

// ================================================
// COURSE COMPLETION MODAL
// ================================================
watch(() => courseStore.completedLessons, (newValue, oldValue) => {
  if (newValue === courseStore.totalLessons && courseStore.totalLessons > 0 && newValue > oldValue) {
    showCompletionModal.value = true
  }
})

const closeCompletionModal = () => {
  showCompletionModal.value = false
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
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  if (removeDelayTimer.value) {
    clearTimeout(removeDelayTimer.value)
  }
  courseStore.clearCourse()
})
</script>