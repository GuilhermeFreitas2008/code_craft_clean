<template>
  <div class="relative">
    <div class="flex gap-2">
      <div class="w-6 h-6 rounded-full bg-primary/5 flex items-center justify-center shrink-0">
        <span class="text-xs font-medium text-primary">{{ reply?.userInitials }}</span>
      </div>
      <div class="flex-1 min-w-0">
        <!-- Nome + seta + @reply + data -->
        <div class="flex items-center gap-1.5 mb-1 flex-wrap">
          <span v-if="currentUserId && reply?.userId === currentUserId" class="font-medium text-foreground text-xs">You</span>
          <span v-else class="font-medium text-foreground text-xs">{{ reply?.userName }}</span>
          
          <!-- Seta estilo TikTok -->
          <span class="text-xs text-foreground/40 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-0.5">
              <path d="M5 12h14"/>
              <path d="m12 5 7 7-7 7"/>
            </svg>
          </span>
          
          <!-- @username de quem está a responder -->
          <span v-if="reply?.replyToUserName" class="text-xs text-primary/80 font-medium">
            @{{ reply?.replyToUserName }}
          </span>
          
          <!-- Data -->
          <span class="text-xs text-foreground/30 whitespace-nowrap">{{ formatDate(reply?.createdAt) }}</span>
        </div>
        
        <!-- Modo de edição -->
        <div v-if="editingLocally && editingLocally.id === reply?.id" class="mt-1">
          <textarea
            v-model="editingLocally.content"
            rows="2"
            class="w-full px-2 py-1 rounded-lg bg-white/5 border border-white/10 focus:border-primary focus:outline-none transition-colors text-foreground text-xs resize-none mb-1"
            :disabled="isEditingComment"
          ></textarea>
          <div class="flex justify-end gap-2">
            <button @click="$emit('cancel-editing')" class="px-2 py-0.5 text-xs text-foreground/60 hover:text-foreground transition-colors" :disabled="isEditingComment">Cancel</button>
            <button @click="$emit('save-edit', reply?.id)" class="px-2 py-0.5 bg-primary text-white rounded-lg text-xs font-medium hover:bg-primary/90 transition-colors flex items-center gap-1 min-w-[50px] justify-center" :disabled="!editingLocally.content?.trim() || isEditingComment">
              <span v-if="isEditingComment && editingCommentId === reply?.id" class="h-2.5 w-2.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
              <span v-else>Save</span>
            </button>
          </div>
        </div>
        
        <p v-else class="text-xs text-foreground/80 break-words whitespace-pre-wrap">{{ reply?.content }}</p>
        
        <!-- Botões de ação -->
        <div class="flex items-center gap-3 mt-2 flex-wrap">
          <!-- Like -->
          <button 
            @click="$emit('like-comment', reply?.id)"
            class="relative group flex items-center gap-1 transition-all duration-200"
            :class="[reply?.isLikedByCurrentUser ? 'text-primary' : 'text-foreground/40 hover:text-primary']"
            :disabled="likingCommentId === reply?.id"
          >
            <div class="relative w-2.5 h-2.5">
              <Heart :size="10" class="absolute inset-0 transition-all duration-300 text-current" :class="reply?.isLikedByCurrentUser ? 'opacity-0' : 'opacity-100'" />
              <Heart :size="10" class="absolute inset-0 transition-all duration-300 fill-current text-primary" :class="[reply?.isLikedByCurrentUser ? 'opacity-100 scale-110' : 'opacity-0 scale-90']" />
              <span v-if="likingCommentId === reply?.id" class="absolute inset-0 animate-ping rounded-full bg-primary/30"></span>
            </div>
            <span class="text-xs transition-all duration-300" :class="[reply?.isLikedByCurrentUser ? 'text-primary font-medium' : 'text-foreground/40']" :key="reply?.likes">{{ reply?.likes || 0 }}</span>
          </button>
          
          <!-- Reply -->
          <button 
            @click="$emit('reply-to', reply)"
            class="text-xs text-foreground/40 hover:text-primary transition-colors"
          >
            Reply
          </button>

          <!-- Edit -->
          <button 
            v-if="currentUserId && reply?.userId === currentUserId"
            @click="$emit('start-editing', reply)"
            class="text-xs text-foreground/40 hover:text-primary transition-colors flex items-center gap-1"
            title="Edit"
            :disabled="isEditingComment || isDeletingComment"
          >
            <PenSquare :size="12" :class="{ 'opacity-50': isEditingComment }" />
            <span>Edit</span>
          </button>
          
          <!-- Delete -->
          <button 
            v-if="currentUserId && reply?.userId === currentUserId"
            @click="$emit('open-delete-modal', reply?.id)"
            class="text-xs text-foreground/40 hover:text-red-500 transition-colors flex items-center gap-1"
            title="Delete"
            :disabled="isDeletingComment || isEditingComment"
          >
            <span v-if="isDeletingComment && deletingCommentId === reply?.id" 
                  class="h-3 w-3 animate-spin rounded-full border-2 border-red-500 border-t-transparent"></span>
            <Trash2 v-else :size="12" :class="{ 'opacity-50': isDeletingComment }" />
            <span>Delete</span>
          </button>

          <!-- Controlos de expansão para respostas deste reply - COM SCROLL CORRIGIDO -->
          <template v-if="reply?.replies?.length">
            <button 
              v-if="!isExpanded"
              @click="handleViewMore"
              class="text-xs text-primary/70 hover:text-primary transition-all duration-200 hover:scale-105 flex items-center gap-1 group"
            >
              <ChevronDown :size="14" class="transition-transform duration-200 group-hover:translate-y-0.5" />
              View more ({{ getHiddenRepliesCount }})
            </button>
            
            <button 
              v-if="isExpanded"
              @click="$emit('toggle-replies', { replyId: reply.id, expanded: false })"
              class="text-xs text-foreground/40 hover:text-foreground/60 transition-all duration-200 hover:scale-105 flex items-center gap-1 group"
            >
              <ChevronUp :size="14" class="transition-transform duration-200 group-hover:-translate-y-0.5" />
              Close all
            </button>
          </template>
        </div>

        <!-- ================================================ -->
        <!-- Respostas deste reply com ID para scroll -->
        <!-- ================================================ -->
        <div 
          v-if="reply?.replies?.length && isExpanded" 
          :id="`nested-replies-${reply.id}`"
          class="relative mt-3 space-y-2 animate-fadeIn"
        >
          <ReplyItem
            v-for="(nestedReply, nestedIndex) in visibleNestedReplies"
            :key="nestedReply.id"
            :reply="nestedReply"
            :current-user-id="currentUserId"
            :is-editing-comment="isEditingComment"
            :editing-comment-id="editingCommentId"
            :is-deleting-comment="isDeletingComment"
            :deleting-comment-id="deletingCommentId"
            :liking-comment-id="likingCommentId"
            :editing-locally="editingLocally"
            :reply-visibility="replyVisibility"
            @reply-to="$emit('reply-to', $event)"
            @like-comment="$emit('like-comment', $event)"
            @start-editing="$emit('start-editing', $event)"
            @cancel-editing="$emit('cancel-editing')"
            @save-edit="$emit('save-edit', $event)"
            @open-delete-modal="$emit('open-delete-modal', $event)"
            @toggle-replies="$emit('toggle-replies', $event)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { PenSquare, Trash2, Heart, ChevronDown, ChevronUp } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{
  reply: any
  currentUserId: number | undefined
  isEditingComment: boolean
  editingCommentId: number | null
  isDeletingComment: boolean
  deletingCommentId: number | null
  likingCommentId: number | null
  editingLocally: { id: number; content: string } | null
  replyVisibility?: Record<number, { expanded: boolean }>
  totalReplies?: number
  currentIndex?: number
}>()

const emit = defineEmits<{
  (e: 'reply-to', comment: any): void
  (e: 'like-comment', commentId: number): void
  (e: 'start-editing', comment: any): void
  (e: 'cancel-editing'): void
  (e: 'save-edit', commentId: number): void
  (e: 'open-delete-modal', commentId: number): void
  (e: 'toggle-replies', payload: { replyId: number; expanded: boolean }): void
}>()

// ================================================
// FUNÇÃO PARA LIDAR COM VIEW MORE E SCROLL
// ================================================
const handleViewMore = () => {
  emit('toggle-replies', { replyId: props.reply.id, expanded: true })
  
  window.setTimeout(() => {
    const element = window.document.getElementById(`nested-replies-${props.reply.id}`)
    if (element) {
      element.scrollIntoView({ 
        behavior: 'smooth', 
        block: 'center'
      })
    }
  }, 150)
}

// ================================================
// VERIFICAR SE ESTE REPLY ESTÁ EXPANDIDO
// ================================================
const isExpanded = computed(() => {
  return props.replyVisibility?.[props.reply.id]?.expanded || false
})

// ================================================
// REPLIES VISÍVEIS DESTE REPLY (só mostra se expandido)
// ================================================
const visibleNestedReplies = computed(() => {
  if (!props.reply?.replies?.length || !isExpanded.value) return []
  
  // Quando expandido, mostra 4 respostas
  return props.reply.replies.slice(0, 4)
})

// ================================================
// CONTAGEM DE RESPOSTAS OCULTAS
// ================================================
const getHiddenRepliesCount = computed(() => {
  if (!props.reply?.replies?.length) return 0
  
  if (isExpanded.value) {
    return Math.max(0, props.reply.replies.length - 4)
  }
  return props.reply.replies.length
})

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