<!-- resources/js/components/profile/ProfileHeader.vue -->
<template>
  <div class="bg-gradient-to-br from-primary/5 to-transparent rounded-2xl p-8 border border-white/5">
    <div class="flex items-start justify-between">
      <div class="flex items-center space-x-6">
        <!-- Avatar com opção de editar -->
        <div class="relative group/avatar">
          <!-- Avatar atual -->
          <div 
            class="h-24 w-24 rounded-full bg-primary/20 flex items-center justify-center text-primary text-4xl font-bold overflow-hidden cursor-pointer relative"
            @click="triggerFileInput"
          >
            <img 
              v-if="avatarUrl"
              :src="avatarUrl" 
              alt="Avatar"
              class="w-full h-full object-cover"
            />
            <span v-else>{{ userInitials }}</span>
            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover/avatar:opacity-100 flex items-center justify-center">
              <Camera :size="24" class="text-white" />
            </div>
          </div>
          <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileSelect" />
          <button 
            v-if="avatarUrl" 
            @click="removeAvatar"
            :disabled="userStore.isLoading"
            class="absolute -bottom-2 -right-2 p-1.5 rounded-full bg-red-500/10 text-red-400 hover:bg-red-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="userStore.isLoading" class="h-3 w-3 animate-spin rounded-full border-2 border-red-400 border-t-transparent"></span>
            <Trash2 v-else :size="16" />
          </button>
        </div>

        <div>
          <h1 class="text-3xl font-bold text-foreground">{{ userStore.user?.name }}</h1>
          <p class="text-foreground/60 mt-1 flex items-center gap-2">
            <Mail :size="14" /> {{ userStore.user?.email }}
          </p>
          <p class="text-foreground/40 text-sm mt-2 flex items-center gap-2">
            <Calendar :size="14" /> Member since {{ formatMemberSince(userStore.user?.created_at) }}
          </p>
        </div>
      </div>

      <div class="flex space-x-2 bg-white/5 rounded-lg p-1">
        <button @click="$emit('tab-change', 'profile')" class="px-4 py-2 rounded-md text-sm font-medium"
          :class="activeTab === 'profile' ? 'bg-primary/20 text-primary' : 'text-foreground/60 hover:text-foreground'">
          <User :size="16" class="inline mr-2" /> Profile
        </button>
        <button @click="$emit('tab-change', 'settings')" class="px-4 py-2 rounded-md text-sm font-medium"
          :class="activeTab === 'settings' ? 'bg-primary/20 text-primary' : 'text-foreground/60 hover:text-foreground'">
          <Settings :size="16" class="inline mr-2" /> Settings
        </button>
      </div>
    </div>

    <!-- Modal CodeCraft Style -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeEditModal"></div>
          
          <div class="relative bg-card border border-white/10 rounded-xl w-[440px] shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="relative px-5 py-4 border-b border-white/5 bg-gradient-to-r from-primary/5 to-transparent">
              <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                  <Camera :size="18" class="text-primary" />
                </div>
                <div>
                  <h3 class="text-base font-semibold text-foreground">Edit Profile Picture</h3>
                  <p class="text-xs text-foreground/40">Crop and adjust your image</p>
                </div>
              </div>
              <button @click="closeEditModal" class="absolute top-4 right-4 w-7 h-7 rounded-lg bg-white/5 hover:bg-white/10 flex items-center justify-center transition-colors">
                <X :size="16" class="text-foreground/60" />
              </button>
            </div>

            <!-- Área do Círculo -->
            <div v-if="tempImage" class="p-6 flex flex-col items-center bg-gradient-to-b from-transparent to-black/20">
              <div class="relative w-48 h-48 mb-5">
                <!-- Container do círculo com glow effect -->
                <div class="absolute inset-0 rounded-full bg-gradient-to-br from-primary/20 to-transparent opacity-50 blur-lg"></div>
                <div class="absolute inset-0 rounded-full border border-primary/30"></div>
                
                <!-- Círculo principal -->
                <div class="absolute inset-0 rounded-full overflow-hidden bg-card border-2 border-primary/30 shadow-xl">
                  <div class="relative w-full h-full cursor-move group/image">
                    <img
                      ref="cropperImage"
                      :src="tempImage"
                      class="absolute inset-0 w-full h-full object-cover transition-transform duration-200"
                      :style="{ transform: `scale(${zoomLevel / 100}) rotate(${rotation}deg)` }"
                      alt="Preview"
                    />
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover/image:opacity-100 transition-opacity"></div>
                  </div>
                </div>

                <!-- Preview pequeno -->
                <div class="absolute -bottom-2 -right-2 w-12 h-12 rounded-full overflow-hidden border-2 border-primary/30 bg-card shadow-lg">
                  <img :src="tempImage" class="w-full h-full object-cover" />
                </div>
              </div>

              <!-- Controles -->
              <div class="w-full space-y-5">
                <!-- Zoom Slider -->
                <div class="space-y-1.5">
                  <div class="flex justify-between text-xs text-foreground/40">
                    <span>Zoom</span>
                    <span class="text-primary">{{ Math.round(zoomLevel) }}%</span>
                  </div>
                  <div class="flex items-center gap-2.5">
                    <ZoomOut :size="15" class="text-foreground/40" />
                    <input 
                      type="range" 
                      min="50" 
                      max="200" 
                      step="1"
                      v-model="zoomLevel" 
                      @input="updateZoom" 
                      class="flex-1 h-1 bg-white/10 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-3.5 [&::-webkit-slider-thumb]:h-3.5 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-primary [&::-webkit-slider-thumb]:cursor-pointer [&::-webkit-slider-thumb]:transition-transform [&::-webkit-slider-thumb]:hover:scale-110"
                    />
                    <ZoomIn :size="15" class="text-foreground/40" />
                  </div>
                </div>

                <!-- Rotation Controls -->
                <div class="space-y-1.5">
                  <div class="flex justify-between text-xs text-foreground/40">
                    <span>Rotation</span>
                    <span class="text-primary">{{ rotation }}°</span>
                  </div>
                  <div class="flex items-center justify-center gap-3">
                    <button @click="rotate(-90)" class="p-2.5 rounded-lg bg-white/5 hover:bg-primary/10 border border-white/10 hover:border-primary/30 transition-all group">
                      <RotateCcw :size="16" class="text-foreground/60 group-hover:text-primary" />
                    </button>
                    <button @click="resetImage" class="px-3 py-1.5 text-xs bg-white/5 text-foreground rounded-lg hover:bg-primary/10 hover:text-primary border border-white/10 hover:border-primary/30 transition-all">
                      Reset
                    </button>
                    <button @click="rotate(90)" class="p-2.5 rounded-lg bg-white/5 hover:bg-primary/10 border border-white/10 hover:border-primary/30 transition-all group">
                      <RotateCw :size="16" class="text-foreground/60 group-hover:text-primary" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="px-5 py-3.5 bg-white/5 border-t border-white/5 flex justify-end gap-2.5">
              <button @click="closeEditModal" class="px-3 py-1.5 text-xs text-foreground/60 hover:text-foreground hover:bg-white/5 rounded-lg transition-colors">
                Cancel
              </button>
              <button 
                @click="saveImage" 
                :disabled="userStore.isLoading"
                class="px-3 py-1.5 text-xs bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="userStore.isLoading" class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                <Check v-else :size="14" />
                <span>{{ userStore.isLoading ? 'Saving...' : 'Save Changes' }}</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { 
  User, Settings, Mail, Calendar, Camera, Trash2, 
  ZoomIn, ZoomOut, RotateCcw, RotateCw, X, Check
} from 'lucide-vue-next'
import { useUserStore } from '@/stores/userStore'

const userStore = useUserStore()

defineProps<{
  activeTab: 'profile' | 'settings'
}>()

defineEmits<{
  (e: 'tab-change', tab: 'profile' | 'settings'): void
}>()

const fileInput = ref<HTMLInputElement | null>(null)
const showEditModal = ref(false)
const tempImage = ref<string | null>(null)
const cropperImage = ref<HTMLImageElement | null>(null)
const zoomLevel = ref(100)
const rotation = ref(0)

// Computed para a URL do avatar (da store)
const avatarUrl = computed(() => userStore.user?.avatar || null)

const userInitials = computed(() => {
  const name = userStore.user?.name
  if (!name) return 'U'
  return name.split(' ').map((n: string) => n[0]).join('').toUpperCase()
})

const formatMemberSince = (date?: string): string => {
  if (!date) return 'Recently'
  return new Date(date).toLocaleDateString('en-US', { 
    month: 'long', 
    year: 'numeric' 
  })
}

const triggerFileInput = (): void => {
  fileInput.value?.click()
}

const handleFileSelect = (event: Event): void => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (e) => {
    tempImage.value = e.target?.result as string
    showEditModal.value = true
    zoomLevel.value = 100
    rotation.value = 0
  }
  reader.readAsDataURL(file)
  target.value = ''
}

const updateZoom = (): void => {
  // O zoom é aplicado via style no template
}

const rotate = (degrees: number): void => {
  rotation.value = (rotation.value + degrees) % 360
}

const resetImage = (): void => {
  zoomLevel.value = 100
  rotation.value = 0
}

const saveImage = async (): Promise<void> => {
  if (!cropperImage.value || !tempImage.value) return

  // Criar canvas com a imagem atual
  const canvas = document.createElement('canvas')
  const ctx = canvas.getContext('2d')
  const size = 300
  
  canvas.width = size
  canvas.height = size

  if (ctx) {
    // Desenhar a imagem do cropper no canvas
    ctx.drawImage(cropperImage.value, 0, 0, size, size)
    
    // Converter canvas para blob
    const blob = await new Promise<Blob>((resolve) => {
      canvas.toBlob((blob) => resolve(blob!), 'image/jpeg', 0.9)
    })
    
    // Criar FormData e fazer upload
    const formData = new FormData()
    formData.append('avatar', blob, 'avatar.jpg')
    
    const result = await userStore.updateAvatar(formData)
    
    if (result.success) {
      closeEditModal()
    }
  }
}

const removeAvatar = async (): Promise<void> => {
  const result = await userStore.removeAvatar()
  if (result.success) {
    // O avatar já foi removido da store
  }
}

const closeEditModal = (): void => {
  showEditModal.value = false
  tempImage.value = null
  zoomLevel.value = 100
  rotation.value = 0
}
</script>

<style scoped>
/* Modal transitions */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.2s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
}
.modal-enter-active .modal-content,
.modal-leave-active .modal-content {
  transition: transform 0.2s ease;
}
.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
  transform: scale(0.95);
}

/* Range input styling */
input[type=range] {
  -webkit-appearance: none;
  appearance: none;
  background: transparent;
}

input[type=range]::-webkit-slider-runnable-track {
  width: 100%;
  height: 4px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 9999px;
}

input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 14px;
  height: 14px;
  background: var(--color-primary);
  border-radius: 50%;
  margin-top: -5px;
  cursor: pointer;
  transition: all 0.2s;
}

input[type=range]::-webkit-slider-thumb:hover {
  transform: scale(1.1);
}

input[type=range]::-moz-range-track {
  width: 100%;
  height: 4px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 9999px;
}

input[type=range]::-moz-range-thumb {
  width: 14px;
  height: 14px;
  background: var(--color-primary);
  border: none;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s;
}

input[type=range]::-moz-range-thumb:hover {
  transform: scale(1.1);
}

input[type=range]:focus {
  outline: none;
}
</style>