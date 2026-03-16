<template>
  <header class="sticky top-0 z-40 border-b border-border bg-background/80 backdrop-blur-xl">
    <div class="flex h-16 items-center justify-between pl-5 pr-8">
      <!-- Lado Esquerdo -->
      <div class="flex items-center space-x-2 lg:space-x-3">
        <!-- Botão Toggle Sidebar -->
        <button 
          @click="$emit('toggle-sidebar')"
          class="hidden rounded-lg pr-2 text-foreground/60 transition-colors hover:bg-white/5 hover:text-foreground lg:block"
          :title="sidebarVisible ? 'Fechar menu' : 'Abrir menu'"
        >
          <component 
            :is="sidebarVisible ? PanelLeftClose : PanelRightClose" 
            :size="29" 
          />
        </button>

        <!-- Linha vertical -->
        <div class="hidden h-16 w-px bg-white/5 lg:block"></div>

        <!-- Mobile Menu Toggle -->
        <button 
          @click="$emit('toggle-mobile-menu')"
          class="rounded-lg p-2 text-foreground/60 hover:bg-white/5 hover:text-foreground lg:hidden"
        >
          <Menu :size="24" />
        </button>
        
        <!-- Logo -->
        <button 
          @click="reloadPage"
          class="flex items-center space-x-1 lg:space-x-1 hover:opacity-80 transition-opacity duration-200"
          title="Reiniciar página"
        >
          <img src="/images/Logo.svg" alt="CodeCraft" class="h-10 w-10 lg:h-14 lg:w-14" />
          <span class="text-2xl font-bold text-foreground lg:text-2xl">Code<span class="text-primary">Craft</span></span>
        </button>
      </div>

      <!-- Lado Direito - Avatar -->
      <div class="relative" ref="userMenuContainer">
        <button 
          ref="userMenuButton"
          @click="toggleUserMenu"
          class="flex items-center space-x-3 rounded-lg p-2 hover:bg-white/5 transition-colors duration-200"
        >
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/20 text-primary">
            <span class="text-sm font-semibold">{{ userStore.user?.name?.charAt(0) || 'U' }}</span>
          </div>
          <span class="hidden text-sm font-medium text-foreground lg:block">{{ userStore.user?.name || 'User' }}</span>
        </button>

        <!-- Popup Menu -->
        <Transition
          enter-active-class="transition-all duration-200 ease-out"
          enter-from-class="opacity-0 scale-95 translate-y-[-8px]"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition-all duration-150 ease-in"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-[-8px]"
        >
          <div 
            v-if="userMenuOpen"
            ref="userMenuPopup"
            class="absolute right-0 top-12 z-50 w-70 rounded-lg border border-border bg-card py-3 shadow-xl origin-top-right"
          >
            <!-- Perfil do Utilizador -->
            <div class="px-1 py-3 border-b border-white/5">
              <div class="flex items-center space-x-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/20 text-primary">
                  <span class="text-lg font-semibold">{{ userStore.user?.name?.charAt(0) || 'U' }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-base font-medium text-foreground">{{ userStore.user?.name || 'User' }}</span>
                  <span class="text-xs text-foreground/40">{{ userStore.user?.email || 'user@example.com' }}</span>
                </div>
              </div>
            </div>

            <!-- Opções do Menu -->
            <div class="py-1">
              <button
                @click="handleProfileClick"
                class="flex w-full items-center space-x-3 px-4 py-2 text-sm text-foreground/80 transition-colors hover:bg-white/5 hover:text-foreground"
              >
                <User :size="18" />
                <span>Profile</span>
              </button>
              
              <button
                @click="handleSettingsClick"
                class="flex w-full items-center space-x-3 px-4 py-2 text-sm text-foreground/80 transition-colors hover:bg-white/5 hover:text-foreground"
              >
                <Settings :size="18" />
                <span>Settings</span>
              </button>
            </div>

            <!-- Logout -->
            <div class="border-t border-white/5 pt-1">
              <button
                @click="handleLogout"
                :disabled="userStore.isLoading"
                class="flex w-full items-center space-x-3 px-5 py-2 text-sm text-red-400 transition-colors hover:bg-white/5 hover:text-red-300 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <LogOut v-if="!userStore.isLoading" :size="18" />
                <svg 
                  v-else
                  class="h-4 w-4 animate-spin text-red-400" 
                  xmlns="http://www.w3.org/2000/svg" 
                  fill="none" 
                  viewBox="0 0 24 24"
                >
                  <circle 
                    class="opacity-25" 
                    cx="12" 
                    cy="12" 
                    r="10" 
                    stroke="currentColor" 
                    stroke-width="4"
                  />
                  <path 
                    class="opacity-75" 
                    fill="currentColor" 
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  />
                </svg>
                <span>{{ userStore.isLoading ? 'Logging out...' : 'Logout' }}</span>
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { Menu, PanelRightClose, PanelLeftClose, User, Settings, LogOut } from 'lucide-vue-next'
import { useUserStore } from '@/stores/userStore'
import { useUiStore } from '@/stores/uiStore'

const userStore = useUserStore()
const uiStore = useUiStore()
const router = useRouter()

// Props
defineProps<{
  sidebarVisible: boolean
}>()

// Emits
defineEmits<{
  (e: 'toggle-mobile-menu'): void
  (e: 'toggle-sidebar'): void
}>()

// User menu state
const userMenuOpen = ref(false)

// Refs
const userMenuButton = ref<HTMLElement | null>(null)
const userMenuPopup = ref<HTMLElement | null>(null)
const userMenuContainer = ref<HTMLElement | null>(null)

// Toggle user menu
const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value
}

// Close user menu
const closeUserMenu = () => {
  userMenuOpen.value = false
}

// Reload page
const reloadPage = () => {
  window.location.reload()
}

// Handlers
const handleProfileClick = () => {
  router.push('/profile')
  closeUserMenu()
}

const handleSettingsClick = () => {
  router.push('/settings')
  closeUserMenu()
}

// Logout
const handleLogout = async () => {
  await userStore.logout()
  closeUserMenu()
}

// Click outside handler
const handleClickOutside = (event: MouseEvent) => {
  if (!userMenuOpen.value) return
  
  if (userMenuContainer.value && !userMenuContainer.value.contains(event.target as Node)) {
    closeUserMenu()
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
button {
  transition: all 200ms cubic-bezier(0.4, 0, 0.2, 1);
}

.origin-top-right {
  transform-origin: top right;
}
</style>