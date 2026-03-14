<!-- components/lessons/ReplyItem.vue -->
<template>
  <div class="p-2">
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
        
        <!-- Botões de ação (like, reply, edit, delete) - TODOS AO MESMO NÍVEL -->
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

          <!-- Edit (só para o próprio user) -->
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
          
          <!-- Delete (só para o próprio user) -->
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
        </div>

        <!-- ================================================ -->
        <!-- Renderizar replies desta reply (recursivo) -->
        <!-- ================================================ -->
        <div v-if="reply?.replies?.length" class="mt-3 space-y-2">
          <ReplyItem
            v-for="nestedReply in reply.replies"
            :key="nestedReply.id"
            :reply="nestedReply"
            :current-user-id="currentUserId"
            :is-editing-comment="isEditingComment"
            :editing-comment-id="editingCommentId"
            :is-deleting-comment="isDeletingComment"
            :deleting-comment-id="deletingCommentId"
            :liking-comment-id="likingCommentId"
            :editing-locally="editingLocally"
            @reply-to="$emit('reply-to', $event)"
            @like-comment="$emit('like-comment', $event)"
            @start-editing="$emit('start-editing', $event)"
            @cancel-editing="$emit('cancel-editing')"
            @save-edit="$emit('save-edit', $event)"
            @open-delete-modal="$emit('open-delete-modal', $event)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { PenSquare, Trash2, Heart } from 'lucide-vue-next'

const props = defineProps<{
  reply: any
  currentUserId: number | undefined
  isEditingComment: boolean
  editingCommentId: number | null
  isDeletingComment: boolean
  deletingCommentId: number | null
  likingCommentId: number | null
  editingLocally: { id: number; content: string } | null
}>()

defineEmits<{
  (e: 'reply-to', comment: any): void
  (e: 'like-comment', commentId: number): void
  (e: 'start-editing', comment: any): void
  (e: 'cancel-editing'): void
  (e: 'save-edit', commentId: number): void
  (e: 'open-delete-modal', commentId: number): void
}>()

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