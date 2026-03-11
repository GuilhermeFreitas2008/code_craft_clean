<!-- pages/logged/courses/LessonDisplay.vue -->
<template>
  <div class="mx-auto max-w-4xl">
    <!-- DEBUG PANEL (opcional) -->
    <div class="fixed top-20 right-4 z-50 bg-black/90 text-white p-4 rounded-lg text-xs">
      <p>activeSection: {{ activeSection || 'null' }}</p>
      <p>comments: {{ comments?.length || 0 }}</p>
      <p>lessonId: {{ lesson?.id }}</p>
    </div>

    <!-- Skeleton Loader -->
    <div v-if="loading" class="space-y-6">
      <div class="h-10 w-3/4 animate-pulse rounded bg-white/5"></div>
      <div class="aspect-video w-full animate-pulse rounded-xl bg-white/5"></div>
      <div class="space-y-3">
        <div class="h-4 w-full animate-pulse rounded bg-white/5"></div>
        <div class="h-4 w-5/6 animate-pulse rounded bg-white/5"></div>
        <div class="h-4 w-4/6 animate-pulse rounded bg-white/5"></div>
      </div>
      <div class="mt-12 pt-6 border-t border-white/5 flex items-center justify-between">
        <div class="flex items-center border border-white/10 rounded-lg overflow-hidden">
          <div class="h-10 w-24 animate-pulse bg-white/5"></div>
          <div class="w-px h-6 bg-white/10"></div>
          <div class="h-10 w-24 animate-pulse bg-white/5"></div>
        </div>
        <div class="h-10 w-32 animate-pulse rounded-lg bg-white/5"></div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <AlertCircle :size="48" class="text-red-400 mx-auto mb-4" />
      <h3 class="text-lg font-semibold text-foreground mb-2">Error loading lesson</h3>
      <p class="text-foreground/60">{{ error }}</p>
      <button 
        @click="$emit('retry')" 
        class="mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90"
      >
        Try Again
      </button>
    </div>

    <!-- No Lesson State -->
    <div v-else-if="!lesson" class="text-center py-12">
      <AlertCircle :size="48" class="text-foreground/40 mx-auto mb-4" />
      <h3 class="text-lg font-semibold text-foreground mb-2">No lesson selected</h3>
      <p class="text-foreground/60">Please select a lesson from the sidebar.</p>
    </div>

    <!-- Conteúdo Real -->
    <div v-else class="space-y-8">
      <!-- Título da Lição -->
      <h1 class="text-3xl font-bold text-foreground">
        {{ lesson.title }}
      </h1>

      <!-- Vídeo Player Personalizado -->
      <div v-if="lesson.video_url" class="mb-8">
        <VideoPlayer 
          :src="lesson.video_url"
          :thumbnail="lesson.thumbnail"
          :autoplay="false"
          aspect-ratio="16:9"
          :show-custom-controls="true"
          @play="$emit('video-play')"
          @pause="$emit('video-pause')"
          @ended="$emit('video-ended')"
          @loaded="$emit('video-loaded')"
        />
      </div>

      <!-- Conteúdo da Lição (Markdown) -->
      <LessonContent :content="lesson.content" />

      <!-- Barra de Ações com Botões -->
      <div class="mt-12 pt-6 border-t border-white/5">
        <div class="flex items-center justify-between">
          <!-- Grupo de Botões Resources + Comments -->
          <div class="flex items-center border border-white/10 rounded-lg overflow-hidden">
            <button
              @click="$emit('toggle-section', 'resources')"
              class="flex items-center gap-2 px-4 py-2 transition-all duration-200"
              :class="activeSection === 'resources' 
                ? 'bg-primary/10 text-primary' 
                : 'text-foreground/60 hover:text-foreground hover:bg-white/5'"
            >
              <Paperclip :size="18" />
              <span class="text-sm font-medium">Resources</span>
              <span v-if="resources?.length" class="ml-1 text-xs bg-primary/20 text-primary px-1.5 rounded-full">
                {{ formatCount(resources.length) }}
              </span>
            </button>

            <div class="w-px h-6 bg-white/10"></div>

            <button
              @click="$emit('toggle-section', 'comments')"
              class="flex items-center gap-2 px-4 py-2 transition-all duration-200"
              :class="activeSection === 'comments' 
                ? 'bg-primary/10 text-primary' 
                : 'text-foreground/60 hover:text-foreground hover:bg-white/5'"
            >
              <MessageCircle :size="18" />
              <span class="text-sm font-medium">Comments</span>
              <span v-if="comments?.length" class="ml-1 text-xs bg-primary/20 text-primary px-1.5 rounded-full">
                {{ formatCount(comments.length) }}
              </span>
            </button>
          </div>

          <!-- Botão de Marcar como Concluída -->
          <button
            @click="$emit('toggle-complete')"
            class="group relative w-[160px] px-6 py-2 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2"
            :class="[
              lesson.completed
                ? canRemoveCompletion
                  ? 'bg-primary/10 text-primary hover:bg-red-500/10 hover:text-red-500 border border-primary/20 hover:border-red-500/20'
                  : 'bg-primary/10 text-primary border border-primary/20 cursor-default opacity-75'
                : 'bg-primary text-white hover:bg-primary/60 shadow-sm shadow-primary/20'
            ]"
            :disabled="lesson.completed && !canRemoveCompletion"
          >
            <template v-if="lesson.completed">
              <template v-if="!canRemoveCompletion">
                <CheckCircle :size="18" />
                <span>Completed</span>
              </template>
              <template v-else>
                <CheckCircle :size="18" class="group-hover:hidden" />
                <span class="group-hover:hidden">Completed</span>
                <XCircle :size="18" class="hidden group-hover:block text-red-500" />
                <span class="hidden group-hover:block">Remove</span>
              </template>
            </template>
            <template v-else>
              <CheckCircle :size="18" />
              <span>Complete</span>
            </template>
          </button>
        </div>

        <!-- Conteúdo Expandido (Recursos ou Comentários) -->
        <Transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 -translate-y-4"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-4"
        >
          <div v-if="activeSection" :key="lesson?.id" class="mt-6">
            <!-- SECÇÃO: Recursos -->
            <div v-if="activeSection === 'resources'" class="bg-white/5 rounded-xl p-6">
              <h3 class="text-lg font-semibold text-foreground mb-4">
                Lesson Resources <span class="text-sm text-foreground/40 ml-2">({{ resources?.length || 0 }})</span>
              </h3>
              
              <div v-if="resources?.length" class="space-y-2">
                <div
                  v-for="resource in resources"
                  :key="resource?.id"
                  class="flex items-center justify-between p-3 rounded-lg bg-white/5 border border-white/10 hover:border-primary/30 transition-colors group"
                >
                  <div class="flex items-center gap-3">
                    <component 
                      :is="getResourceIcon(resource?.type)" 
                      :size="18" 
                      class="text-primary/70"
                    />
                    <div>
                      <h4 class="font-medium text-foreground text-sm">{{ resource?.title }}</h4>
                      <p v-if="resource?.size" class="text-xs text-foreground/40">{{ resource.size }}</p>
                    </div>
                  </div>
                  <a :href="resource?.url" target="_blank" class="p-2 rounded-lg hover:bg-primary/10 transition-colors">
                    <Download :size="16" class="text-foreground/40 group-hover:text-primary" />
                  </a>
                </div>
              </div>
              <p v-else class="text-center text-foreground/40 py-8">No resources available for this lesson yet.</p>
            </div>

            <!-- SECÇÃO: Comentários - COM EDIÇÃO/APAGAR -->
            <div v-if="activeSection === 'comments'" class="bg-white/5 rounded-xl p-6">
              <h3 class="text-lg font-semibold text-foreground mb-4">
                Comments <span class="text-sm text-foreground/40 ml-2">({{ comments?.length || 0 }})</span>
              </h3>
              
              <!-- Formulário de Novo Comentário -->
              <div class="flex gap-3 mb-6">
                <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center shrink-0">
                  <span class="text-xs font-medium text-primary">{{ userInitials }}</span>
                </div>
                <div class="flex-1">
                  <div v-if="replyingTo" class="mb-2 text-xs text-primary bg-primary/10 px-2 py-1 rounded-lg inline-flex items-center gap-1">
                    <span>Replying to {{ replyingTo?.userId === currentUserId ? 'yourself' : replyingTo?.userName }}</span>
                    <button @click="$emit('cancel-reply')" class="hover:text-primary/80">✕</button>
                  </div>
                  <textarea
                    :value="newComment || ''"
                    @input="$emit('update:new-comment', ($event.target as HTMLTextAreaElement).value)"
                    rows="2"
                    :placeholder="replyingTo ? 'Write your reply...' : 'Share your thoughts...'"
                    class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 focus:border-primary focus:outline-none transition-colors text-foreground placeholder:text-foreground/40 text-sm resize-none"
                  ></textarea>
                  
                  <div class="flex justify-end gap-2 mt-2">
                    <button
                      v-if="newComment"
                      @click="$emit('clear-comment')"
                      class="px-3 py-1 text-xs text-foreground/60 hover:text-foreground transition-colors"
                    >
                      Cancel
                    </button>
                    <button
                      @click="$emit('submit-comment', newComment)"
                      :disabled="!newComment?.trim() || commentSubmitting"
                      class="px-3 py-1 bg-primary text-white rounded-lg text-xs font-medium hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1"
                    >
                      <span v-if="commentSubmitting" class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                      <span>Post</span>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Lista de Comentários - COM "YOU" E AÇÕES -->
              <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                <div
                  v-for="comment in comments || []"
                  :key="comment?.id"
                  class="flex gap-3 group"
                >
                  <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <span class="text-xs font-medium text-primary">{{ comment?.userInitials }}</span>
                  </div>
                  
                  <div class="flex-1">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-2 mb-1">
                        <span v-if="currentUserId && comment?.userId === currentUserId" class="font-medium text-foreground text-sm">You</span>
                        <span v-else class="font-medium text-foreground text-sm">{{ comment?.userName }}</span>
                        <span class="text-xs text-foreground/40">{{ formatDate(comment?.createdAt) }}</span>
                      </div>
                      
                      <!-- Botões de ação (só aparecem para o próprio user) -->
                      <div v-if="currentUserId && comment?.userId === currentUserId" class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button 
                          @click="startEditing(comment)"
                          class="p-1 text-foreground/40 hover:text-primary transition-colors"
                          title="Edit"
                        >
                          <PenSquare :size="14" />
                        </button>
                        <button 
                          @click="openDeleteModal(comment?.id)"
                          class="p-1 text-foreground/40 hover:text-red-500 transition-colors"
                          title="Delete"
                        >
                          <Trash2 :size="14" />
                        </button>
                      </div>
                    </div>
                    
                    <!-- Modo de edição -->
                    <div v-if="editingCommentId === comment?.id" class="mt-2">
                      <textarea
                        v-model="editingContent"
                        rows="2"
                        class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 focus:border-primary focus:outline-none transition-colors text-foreground text-sm resize-none mb-2"
                      ></textarea>
                      <div class="flex justify-end gap-2">
                        <button 
                          @click="cancelEditing"
                          class="px-2 py-1 text-xs text-foreground/60 hover:text-foreground transition-colors"
                        >
                          Cancel
                        </button>
                        <button 
                          @click="saveEdit(comment?.id)"
                          class="px-2 py-1 bg-primary text-white rounded-lg text-xs font-medium hover:bg-primary/90 transition-colors"
                          :disabled="!editingContent?.trim()"
                        >
                          Save
                        </button>
                      </div>
                    </div>
                    
                    <!-- Modo de visualização -->
                    <p v-else class="text-sm text-foreground/80">{{ comment?.content }}</p>
                    
                    <div class="flex items-center gap-3 mt-2">
                      <!-- Usar handleLikeComment em vez de emit direto -->
                      <button 
                        @click="handleLikeComment(comment?.id)"
                        class="text-xs text-foreground/40 hover:text-primary transition-colors flex items-center gap-1"
                      >
                        <Heart :size="12" />
                        {{ comment?.likes || 0 }}
                      </button>
                      <!-- Usar handleReplyTo em vez de emit direto -->
                      <button 
                        @click="handleReplyTo(comment)"
                        class="text-xs text-foreground/40 hover:text-primary transition-colors"
                      >
                        Reply
                      </button>
                    </div>

                    <!-- Respostas - COM "YOU" E AÇÕES -->
                    <div v-if="comment?.replies?.length" class="ml-6 mt-3 space-y-3">
                      <div
                        v-for="reply in comment.replies"
                        :key="reply?.id"
                        class="flex gap-2 group/reply"
                      >
                        <div class="w-6 h-6 rounded-full bg-primary/5 flex items-center justify-center shrink-0">
                          <span class="text-xs font-medium text-primary">{{ reply?.userInitials }}</span>
                        </div>
                        <div class="flex-1">
                          <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 mb-1">
                              <span v-if="currentUserId && reply?.userId === currentUserId" class="font-medium text-foreground text-xs">You</span>
                              <span v-else class="font-medium text-foreground text-xs">{{ reply?.userName }}</span>
                              <span class="text-xs text-foreground/40">{{ formatDate(reply?.createdAt) }}</span>
                            </div>
                            
                            <!-- Botões de ação para respostas -->
                            <div v-if="currentUserId && reply?.userId === currentUserId" class="flex items-center gap-1 opacity-0 group-hover/reply:opacity-100 transition-opacity">
                              <button 
                                @click="startEditing(reply)"
                                class="p-1 text-foreground/40 hover:text-primary transition-colors"
                                title="Edit"
                              >
                                <PenSquare :size="12" />
                              </button>
                              <button 
                                @click="openDeleteModal(reply?.id)"
                                class="p-1 text-foreground/40 hover:text-red-500 transition-colors"
                                title="Delete"
                              >
                                <Trash2 :size="12" />
                              </button>
                            </div>
                          </div>
                          
                          <!-- Modo de edição para respostas -->
                          <div v-if="editingCommentId === reply?.id" class="mt-1">
                            <textarea
                              v-model="editingContent"
                              rows="2"
                              class="w-full px-2 py-1 rounded-lg bg-white/5 border border-white/10 focus:border-primary focus:outline-none transition-colors text-foreground text-xs resize-none mb-1"
                            ></textarea>
                            <div class="flex justify-end gap-2">
                              <button 
                                @click="cancelEditing"
                                class="px-2 py-0.5 text-xs text-foreground/60 hover:text-foreground transition-colors"
                              >
                                Cancel
                              </button>
                              <button 
                                @click="saveEdit(reply?.id)"
                                class="px-2 py-0.5 bg-primary text-white rounded-lg text-xs font-medium hover:bg-primary/90 transition-colors"
                                :disabled="!editingContent?.trim()"
                              >
                                Save
                              </button>
                            </div>
                          </div>
                          
                          <!-- Modo de visualização -->
                          <p v-else class="text-xs text-foreground/80">{{ reply?.content }}</p>
                          
                          <div class="flex items-center gap-3 mt-1">
                            <button 
                              @click="handleLikeComment(reply?.id)"
                              class="text-xs text-foreground/40 hover:text-primary transition-colors flex items-center gap-1"
                            >
                              <Heart :size="10" />
                              {{ reply?.likes || 0 }}
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Mensagem se não houver comentários -->
                <div v-if="!comments?.length" class="text-center text-foreground/40 py-8">
                  No comments yet. Be the first to comment!
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Modal de Confirmação de Delete (direto aqui no componente) -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeDeleteModal"></div>
        
        <!-- Modal -->
        <div class="relative bg-background border border-white/10 rounded-xl max-w-md w-full p-6 shadow-2xl">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-500/10 flex items-center justify-center">
              <AlertCircle class="w-5 h-5 text-red-500" />
            </div>
            <h3 class="text-lg font-semibold text-foreground">Delete Comment</h3>
          </div>
          
          <p class="text-foreground/80 mb-6">Are you sure you want to delete this comment? This action cannot be undone.</p>
          
          <div class="flex justify-end gap-3">
            <button
              @click="closeDeleteModal"
              class="px-4 py-2 rounded-lg text-foreground/60 hover:text-foreground hover:bg-white/5 transition-colors"
            >
              Cancel
            </button>
            <button
              @click="confirmDelete"
              class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors flex items-center gap-2"
            >
              <Trash2 :size="16" />
              Delete
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { 
  CheckCircle, 
  XCircle, 
  Paperclip,
  FileText,
  Image,
  File,
  Download,
  MessageCircle,
  Heart,
  AlertCircle,
  PenSquare,
  Trash2
} from 'lucide-vue-next'
import VideoPlayer from '@/components/lessons/VideoPlayer.vue'
import LessonContent from '@/components/lessons/LessonContent.vue'
import { useUserStore } from '@/stores/userStore'
import { ref } from 'vue'

const props = defineProps<{
  lesson: any
  resources: any[]
  comments: any[]
  loading: boolean
  error: string | null
  activeSection: 'resources' | 'comments' | null
  canRemoveCompletion: boolean
  userInitials: string
  replyingTo: any | null
  commentSubmitting: boolean
  newComment: string
}>()

const emit = defineEmits<{
  (e: 'retry'): void
  (e: 'video-play'): void
  (e: 'video-pause'): void
  (e: 'video-ended'): void
  (e: 'video-loaded'): void
  (e: 'toggle-section', section: 'resources' | 'comments'): void
  (e: 'toggle-complete'): void
  (e: 'update:new-comment', value: string): void
  (e: 'submit-comment', content: string): void
  (e: 'like-comment', commentId: number): void
  (e: 'reply-to', comment: any): void
  (e: 'delete-comment', commentId: number): void
  (e: 'edit-comment', commentId: number, content: string): void
  (e: 'cancel-reply'): void
  (e: 'clear-comment'): void
}>()

const userStore = useUserStore()
const currentUserId = userStore.user?.id

// Estado para edição
const editingCommentId = ref<number | null>(null)
const editingContent = ref('')

// Estado para o modal de delete
const showDeleteModal = ref(false)
const commentToDelete = ref<number | null>(null)

// ================================================
// FUNÇÕES DO MODAL DE DELETE
// ================================================
const openDeleteModal = (commentId: number | null | undefined) => {
  if (!commentId) return
  commentToDelete.value = commentId
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  commentToDelete.value = null
}

const confirmDelete = () => {
  if (commentToDelete.value) {
    emit('delete-comment', commentToDelete.value)
  }
  closeDeleteModal()
}

// ================================================
// FUNÇÕES AUXILIARES (validam antes de emitir)
// ================================================
const handleLikeComment = (commentId: number | null | undefined) => {
  if (!commentId) return
  emit('like-comment', commentId)
}

const handleReplyTo = (comment: any) => {
  if (!comment?.id) return
  emit('reply-to', comment)
}

// ================================================
// FUNÇÕES DE EDIÇÃO
// ================================================
const startEditing = (comment: any) => {
  if (!comment?.id) return
  editingCommentId.value = comment.id
  editingContent.value = comment.content || ''
}

const cancelEditing = () => {
  editingCommentId.value = null
  editingContent.value = ''
}

const saveEdit = (commentId: number | null | undefined) => {
  if (!commentId) return
  if (!editingContent.value?.trim()) return
  
  emit('edit-comment', commentId, editingContent.value)
  editingCommentId.value = null
  editingContent.value = ''
}

// ================================================
// FORMAT COUNT
// ================================================
const formatCount = (count: number): string => {
  if (count > 99) return '+99'
  return count.toString()
}

// ================================================
// GET RESOURCE ICON
// ================================================
const getResourceIcon = (type: string) => {
  const icons: Record<string, any> = {
    pdf: FileText,
    image: Image,
    presentation: File,
    archive: File,
    default: Paperclip
  }
  return icons[type] || icons.default
}

// ================================================
// FORMAT DATE
// ================================================
const formatDate = (date: Date | string | null | undefined) => {
  if (!date) return 'recently'
  
  try {
    const dateObj = typeof date === 'string' ? new Date(date) : date
    const now = new Date()
    const diffTime = dateObj.getTime() - now.getTime()
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    
    return new Intl.RelativeTimeFormat('en', { numeric: 'auto' }).format(diffDays, 'day')
  } catch (e) {
    console.error('Erro ao formatar data:', e, date)
    return 'recently'
  }
}
</script>