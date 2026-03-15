<!-- pages/logged/courses/LessonDisplay.vue -->
<template>
  <div class="mx-auto max-w-4xl">
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

            <!-- SECÇÃO: Comentários - COM ANIMAÇÕES SUAVES -->
            <div v-if="activeSection === 'comments'" class="bg-white/5 rounded-xl p-6">
              <h3 class="text-lg font-semibold text-foreground mb-4">
                Comments <span class="text-sm text-foreground/40 ml-2">({{ comments?.length || 0 }})</span>
              </h3>
              
              <!-- Formulário de Novo Comentário com ref para scroll -->
              <div class="flex gap-3 mb-6 pb-4 border-b border-white/5" ref="commentFormRef">
                <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center shrink-0">
                  <span class="text-xs font-medium text-primary">{{ userInitials }}</span>
                </div>
                <div class="flex-1">
                  <!-- Badge de Reply (com deteção de self-reply) -->
                  <div v-if="replyingTo" class="mb-2 flex items-center gap-2">
                    <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded-lg inline-flex items-center gap-1">
                      <span>
                        Replying to 
                        <span v-if="replyingTo?.userId === currentUserId" class="font-semibold text-primary">
                          yourself
                        </span>
                        <span v-else class="font-semibold text-primary">
                          @{{ replyingTo?.userName }}
                        </span>
                      </span>
                      <button @click="$emit('cancel-reply')" class="hover:text-primary/80 ml-1">✕</button>
                    </span>
                  </div>
                  
                  <!-- Textarea livre com ref -->
                  <textarea
                    :value="newComment || ''"
                    @input="$emit('update:new-comment', ($event.target as HTMLTextAreaElement).value)"
                    @keydown.enter.prevent="handleEnterKey"
                    rows="3"
                    :placeholder="replyingTo ? 'Write your reply...' : 'Share your thoughts...'"
                    class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 focus:border-primary focus:outline-none transition-colors text-foreground placeholder:text-foreground/40 text-sm resize-none"
                    ref="textareaRef"
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

              <!-- Lista de Comentários Principais (máx 5) - SEM SEPARADORES -->
              <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                <div
                  v-for="(comment, index) in visibleComments"
                  :key="comment?.id"
                  class="relative"
                >
                  <div class="flex gap-3">
                    <!-- Avatar -->
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                      <span class="text-xs font-medium text-primary">{{ comment?.userInitials }}</span>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                      <!-- Nome + data -->
                      <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span v-if="currentUserId && comment?.userId === currentUserId" class="font-medium text-foreground text-sm">You</span>
                        <span v-else class="font-medium text-foreground text-sm">{{ comment?.userName }}</span>
                        <span class="text-xs text-foreground/30 whitespace-nowrap">{{ formatDate(comment?.createdAt) }}</span>
                      </div>
                      
                      <!-- Modo de edição -->
                      <div v-if="editingLocally && editingLocally.id === comment?.id" class="mt-2">
                        <textarea
                          v-model="editingLocally.content"
                          rows="2"
                          class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 focus:border-primary focus:outline-none transition-colors text-foreground text-sm resize-none mb-2"
                          :disabled="isEditingComment"
                        ></textarea>
                        <div class="flex justify-end gap-2">
                          <button 
                            @click="cancelEditing"
                            class="px-2 py-1 text-xs text-foreground/60 hover:text-foreground transition-colors"
                            :disabled="isEditingComment"
                          >
                            Cancel
                          </button>
                          <button 
                            @click="saveEdit(comment?.id)"
                            class="px-2 py-1 bg-primary text-white rounded-lg text-xs font-medium hover:bg-primary/90 transition-colors flex items-center gap-1 min-w-[60px] justify-center"
                            :disabled="!editingLocally.content?.trim() || isEditingComment"
                          >
                            <span v-if="isEditingComment && editingCommentId === comment?.id" class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            <span v-else>Save</span>
                          </button>
                        </div>
                      </div>
                      
                      <!-- Modo de visualização -->
                      <p v-else class="text-sm text-foreground/80 break-words whitespace-pre-wrap mb-2">{{ comment?.content }}</p>
                      
                      <!-- Botões de ação -->
                      <div class="flex items-center gap-3 mt-1 flex-wrap">
                        <!-- Like -->
                        <button 
                          @click="handleLikeComment(comment?.id)"
                          class="relative group flex items-center gap-1 transition-all duration-200"
                          :class="[
                            comment?.isLikedByCurrentUser 
                              ? 'text-primary' 
                              : 'text-foreground/40 hover:text-primary'
                          ]"
                          :disabled="likingCommentId === comment?.id"
                        >
                          <div class="relative w-3 h-3">
                            <Heart 
                              :size="12" 
                              class="absolute inset-0 transition-all duration-300 text-current"
                              :class="comment?.isLikedByCurrentUser ? 'opacity-0' : 'opacity-100'"
                            />
                            <Heart 
                              :size="12" 
                              class="absolute inset-0 transition-all duration-300 fill-current text-primary"
                              :class="[
                                comment?.isLikedByCurrentUser 
                                  ? 'opacity-100 scale-110' 
                                  : 'opacity-0 scale-90'
                              ]"
                            />
                            <span 
                              v-if="likingCommentId === comment?.id"
                              class="absolute inset-0 animate-ping rounded-full bg-primary/30"
                            ></span>
                          </div>
                          <span 
                            class="text-xs transition-all duration-300"
                            :class="[
                              comment?.isLikedByCurrentUser 
                                ? 'text-primary font-medium scale-110' 
                                : 'text-foreground/40'
                            ]"
                            :key="comment?.likes"
                          >
                            {{ comment?.likes || 0 }}
                          </span>
                        </button>
                        
                        <!-- Reply -->
                        <button 
                          @click="handleReplyTo(comment)"
                          class="text-xs text-foreground/40 hover:text-primary transition-colors"
                        >
                          Reply
                        </button>

                        <!-- Edit -->
                        <button 
                          v-if="currentUserId && comment?.userId === currentUserId"
                          @click="startEditing(comment)"
                          class="text-xs text-foreground/40 hover:text-primary transition-colors flex items-center gap-1"
                          title="Edit"
                          :disabled="isEditingComment || isDeletingComment"
                        >
                          <PenSquare :size="12" :class="{ 'opacity-50': isEditingComment }" />
                          <span>Edit</span>
                        </button>
                        
                        <!-- Delete -->
                        <button 
                          v-if="currentUserId && comment?.userId === currentUserId"
                          @click="openDeleteModal(comment?.id)"
                          class="text-xs text-foreground/40 hover:text-red-500 transition-colors flex items-center gap-1"
                          title="Delete"
                          :disabled="isDeletingComment || isEditingComment"
                        >
                          <span v-if="isDeletingComment && deletingCommentId === comment?.id" 
                                class="h-3 w-3 animate-spin rounded-full border-2 border-red-500 border-t-transparent"></span>
                          <Trash2 v-else :size="12" :class="{ 'opacity-50': isDeletingComment }" />
                          <span>Delete</span>
                        </button>

                        <!-- Controlos de expansão para replies do comentário principal - COM ANIMAÇÕES SIMPLES -->
                        <template v-if="comment?.replies?.length">
                          <button 
                            v-if="!isCommentExpanded(comment.id) && comment.replies.length > 2"
                            @click="toggleCommentReplies(comment.id, true)"
                            class="text-xs text-primary/70 hover:text-primary transition-all duration-200 hover:scale-105 flex items-center gap-1 group"
                          >
                            <ChevronDown :size="14" class="transition-transform duration-200 group-hover:translate-y-0.5" />
                            View more ({{ getHiddenRepliesCount(comment) }})
                          </button>
                          
                          <button 
                            v-if="isCommentExpanded(comment.id)"
                            @click="toggleCommentReplies(comment.id, false)"
                            class="text-xs text-foreground/40 hover:text-foreground/60 transition-all duration-200 hover:scale-105 flex items-center gap-1 group"
                          >
                            <ChevronUp :size="14" class="transition-transform duration-200 group-hover:-translate-y-0.5" />
                            Close all
                          </button>
                        </template>
                      </div>

                      <!-- Replies do comentário principal (2 ou expandido) - COM ANIMAÇÃO FADE -->
                      <div 
                        v-if="comment?.replies?.length" 
                        class="mt-3 space-y-2"
                      >
                        <ReplyItem
                          v-for="(reply, replyIndex) in getVisibleReplies(comment)"
                          :key="reply.id"
                          :reply="reply"
                          :current-user-id="currentUserId"
                          :is-editing-comment="isEditingComment"
                          :editing-comment-id="editingCommentId"
                          :is-deleting-comment="isDeletingComment"
                          :deleting-comment-id="deletingCommentId"
                          :liking-comment-id="likingCommentId"
                          :editing-locally="editingLocally"
                          :reply-visibility="replyRepliesVisibility"
                          :total-replies="comment.replies.length"
                          :current-index="Number(replyIndex)"
                          @reply-to="handleReplyTo"
                          @like-comment="handleLikeComment"
                          @start-editing="startEditing"
                          @cancel-editing="cancelEditing"
                          @save-edit="saveEdit"
                          @open-delete-modal="openDeleteModal"
                          @toggle-replies="toggleReplyReplies"
                        />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Botão View More Comments - COM ANIMAÇÃO -->
                <div v-if="comments && comments.length > 5" class="flex justify-center mt-4">
                  <button
                    @click="showMoreComments"
                    class="px-4 py-2 text-xs text-primary/70 hover:text-primary transition-all duration-200 hover:scale-105 flex items-center gap-2 group"
                  >
                    <ChevronDown :size="16" class="transition-transform duration-200 group-hover:translate-y-1" />
                    View More Comments ({{ comments.length - 5 }} more)
                  </button>
                </div>
                
                <div v-if="!comments?.length" class="text-center text-foreground/40 py-8">
                  No comments yet. Be the first to comment!
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Modal de Confirmação de Delete com LOADING -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="!isDeletingComment && closeDeleteModal()"></div>
        
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
              :disabled="isDeletingComment"
            >
              Cancel
            </button>
            <button 
              @click="confirmDelete" 
              class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors flex items-center gap-2 min-w-[80px] justify-center" 
              :disabled="isDeletingComment"
            >
              <span v-if="isDeletingComment && deletingCommentId === commentToDelete" 
                    class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
              <span v-else>Delete</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { 
  CheckCircle, XCircle, Paperclip, FileText, Image, File,
  Download, MessageCircle, Heart, AlertCircle, PenSquare, Trash2,
  ChevronDown, ChevronUp
} from 'lucide-vue-next'
import VideoPlayer from '@/components/lessons/VideoPlayer.vue'
import LessonContent from '@/components/lessons/LessonContent.vue'
import ReplyItem from '@/components/lessons/ReplyItem.vue'
import { useUserStore } from '@/stores/userStore'
import { ref, watch, nextTick, computed } from 'vue'

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
  isEditingComment: boolean
  editingCommentId: number | null
  isDeletingComment: boolean
  deletingCommentId: number | null
  likingCommentId: number | null
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

// Estados
const editingLocally = ref<{ id: number; content: string } | null>(null)
const showDeleteModal = ref(false)
const commentToDelete = ref<number | null>(null)

// Estado para controlo de paginação de comentários principais
const commentsPage = ref(1)
const COMMENTS_PER_PAGE = 5

// Estado para controlar expansão de replies de comentários principais
const commentRepliesExpanded = ref<Record<number, boolean>>({})

// Estado para controlar visibilidade das replies de replies
const replyRepliesVisibility = ref<Record<number, { expanded: boolean }>>({})

// Referências para elementos
const textareaRef = ref<HTMLTextAreaElement | null>(null)
const commentFormRef = ref<HTMLElement | null>(null)

// ================================================
// COMPUTED - Comentários visíveis (paginados)
// ================================================
const visibleComments = computed(() => {
  if (!props.comments?.length) return []
  return props.comments.slice(0, commentsPage.value * COMMENTS_PER_PAGE)
})

// ================================================
// FUNÇÕES PARA REPLIES DE COMENTÁRIOS PRINCIPAIS
// ================================================
const isCommentExpanded = (commentId: number): boolean => {
  return commentRepliesExpanded.value[commentId] || false
}

const toggleCommentReplies = (commentId: number, expanded: boolean) => {
  commentRepliesExpanded.value[commentId] = expanded
}

const getVisibleReplies = (comment: any) => {
  if (!comment?.replies?.length) return []
  
  // Se expandido, mostra 6 (2 iniciais + 4 extra)
  if (isCommentExpanded(comment.id)) {
    return comment.replies.slice(0, 6)
  }
  // Se não expandido, mostra apenas 2
  return comment.replies.slice(0, 2)
}

const getHiddenRepliesCount = (comment: any): number => {
  if (!comment?.replies?.length) return 0
  
  if (isCommentExpanded(comment.id)) {
    return Math.max(0, comment.replies.length - 6)
  }
  return Math.max(0, comment.replies.length - 2)
}

// ================================================
// FUNÇÕES PARA REPLIES DE REPLIES
// ================================================
const toggleReplyReplies = (payload: { replyId: number; expanded: boolean }) => {
  replyRepliesVisibility.value[payload.replyId] = { expanded: payload.expanded }
}

// ================================================
// FUNÇÃO PARA MOSTRAR MAIS COMENTÁRIOS
// ================================================
const showMoreComments = () => {
  commentsPage.value++
}

// ================================================
// WATCH PARA LIMPAR ESTADO LOCAL QUANDO A EDIÇÃO TERMINA
// ================================================
watch(() => props.editingCommentId, (newId) => {
  if (newId === null && editingLocally.value) {
    editingLocally.value = null
  }
})

// ================================================
// FUNÇÕES DO MODAL DE DELETE
// ================================================
const openDeleteModal = (commentId: number | null | undefined) => {
  if (!commentId) return
  if (props.isDeletingComment) return
  
  commentToDelete.value = commentId
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  if (props.isDeletingComment) return
  showDeleteModal.value = false
  commentToDelete.value = null
}

const confirmDelete = () => {
  if (commentToDelete.value && !props.isDeletingComment) {
    emit('delete-comment', commentToDelete.value)
  }
}

// Observar quando o comentário é removido para fechar o modal
watch(() => props.comments, () => {
  if (commentToDelete.value && !props.comments?.some((c: any) => c.id === commentToDelete.value)) {
    showDeleteModal.value = false
    commentToDelete.value = null
  }
}, { deep: true })

// ================================================
// FUNÇÕES AUXILIARES - COM SCROLL
// ================================================
const handleLikeComment = (commentId: number | null | undefined) => {
  if (!commentId) return
  emit('like-comment', commentId)
}

const handleReplyTo = (comment: any) => {
  if (!comment?.id) return
  emit('reply-to', comment)
  
  // Scroll suave para a textarea com foco
  nextTick(() => {
    if (textareaRef.value) {
      textareaRef.value.focus()
      setTimeout(() => {
        commentFormRef.value?.scrollIntoView({ 
          behavior: 'smooth', 
          block: 'center'
        })
      }, 100)
    }
  })
}

// ================================================
// FUNÇÕES DE EDIÇÃO
// ================================================
const startEditing = (comment: any) => {
  if (!comment?.id) return
  if (props.isEditingComment || props.isDeletingComment) return
  editingLocally.value = { id: comment.id, content: comment.content || '' }
}

const cancelEditing = () => {
  editingLocally.value = null
}

const saveEdit = (commentId: number | null | undefined) => {
  if (!commentId) return
  if (!editingLocally.value?.content?.trim()) return
  if (props.isEditingComment) return
  emit('edit-comment', commentId, editingLocally.value.content)
}

// ================================================
// FUNÇÃO PARA ENTER
// ================================================
const handleEnterKey = () => {
  if (props.newComment?.trim() && !props.commentSubmitting) {
    emit('submit-comment', props.newComment)
  }
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
    pdf: FileText, image: Image, presentation: File, archive: File, default: Paperclip
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
    return 'recently'
  }
}
</script>

<style scoped>
@keyframes like-pop {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}
.like-animation {
  animation: like-pop 0.3s ease-out;
}

/* Para o placeholder */
textarea::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

/* Para manter o whitespace no texto */
.whitespace-pre-wrap {
  white-space: pre-wrap;
  word-wrap: break-word;
}

/* Animações suaves */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.2s ease-out;
}
</style>