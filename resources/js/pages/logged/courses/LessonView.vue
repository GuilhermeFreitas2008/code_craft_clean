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
            @update:new-comment="newComment = $event"
            @retry="fetchLessonData"
            @video-play="onVideoPlay"
            @video-pause="onVideoPause"
            @video-ended="onVideoEnded"
            @video-loaded="onVideoLoaded"
            @toggle-section="toggleSection"
            @toggle-complete="toggleLessonComplete"
            @submit-comment="submitComment"
            @like-comment="likeComment"
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
import { useCourseStore } from '@/stores/courseStore'
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
    const userId = comment.user_id || comment.user?.id
    const userName = comment.user?.username || comment.userName || 'Unknown'
    const userInitials = comment.user?.username?.charAt(0).toUpperCase() || 
                        comment.userInitials || 
                        userName.charAt(0).toUpperCase()
    
    const mappedReplies = comment.replies?.map((reply: any) => mapComment(reply))

    return {
      id: comment.id,
      userId: userId,
      userName: userName,
      userInitials: userInitials,
      content: comment.content,
      createdAt: comment.created_at || comment.createdAt,
      likes: comment.likes || 0,
      isLikedByCurrentUser: false,
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
  console.log('🎯 toggleSection called with:', section)
  console.log('🎯 current activeSection:', activeSection.value)
  
  if (activeSection.value === section) {
    activeSection.value = null
  } else {
    activeSection.value = section
  }
  
  console.log('🎯 new activeSection:', activeSection.value)
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
  
  // Garantir que parentId é number | null (nunca undefined)
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

// Aceitar number | undefined e validar
const likeComment = async (commentId: number | undefined) => {
  if (!commentId) return
  await courseStore.likeComment(commentId)
  if (courseStore.currentLessonId) {
    await courseStore.fetchLessonComments(courseStore.currentLessonId)
  }
}

// ================================================
// EDIT COMMENT
// ================================================
const handleEditComment = async (commentId: number, content: string) => {
  if (!commentId || !content?.trim() || !courseStore.currentLessonId) return
  
  const result = await courseStore.editComment(commentId, content)
  
  if (result?.success) {
    await courseStore.fetchLessonComments(courseStore.currentLessonId)
  }
}

// ================================================
// DELETE COMMENT
// ================================================
const handleDeleteComment = async (commentId: number) => {
  if (!commentId || !courseStore.currentLessonId) return
  
  const result = await courseStore.deleteComment(commentId)
  
  if (result?.success) {
    await courseStore.fetchLessonComments(courseStore.currentLessonId)
  }
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

const continueLearning = () => {
  closeCompletionModal()
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
  console.log('🎯 handleLessonSelect:', lessonId)
  
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
  console.log('🎯 fetchLessonData iniciado')
  
  if (courseId) {
    await courseStore.fetchCourse(courseId)
    
    if (courseStore.modules?.length > 0) {
      const lessonId = initialLessonId || courseStore.modules[0]?.lessons[0]?.id
      
      if (lessonId) {
        courseStore.selectLesson(lessonId)
        console.log('🎯 fetching resources and comments for lesson:', lessonId)
        
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
  console.log('🎯 LessonView onMounted')
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