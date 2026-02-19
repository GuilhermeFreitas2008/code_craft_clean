<template>
  <header class="sticky top-0 z-40 border-b border-border bg-background/80 backdrop-blur-xl">
    <div class="flex h-16 items-center justify-between pl-5 pr-8">
      <!-- Lado Esquerdo - Botão Toggle, Logo e Mobile Menu -->
      <div class="flex items-center space-x-2 lg:space-x-3">
        <!-- Botão Toggle Sidebar (apenas desktop) -->
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

        <!-- Linha vertical de separação -->
        <div class="hidden h-16 w-px bg-white/20 lg:block"></div>

        <!-- Mobile Menu Toggle -->
        <button 
          @click="$emit('toggle-mobile-menu')"
          class="rounded-lg p-2 text-foreground/60 hover:bg-white/5 hover:text-foreground lg:hidden"
        >
          <Menu :size="24" />
        </button>
        
        <!-- Logo -->
        <div class="flex items-center space-x-1 lg:space-x-1">
          <img src="/images/Logo.svg" alt="CodeCraft" class="h-10 w-10 lg:h-14 lg:w-14" />
          <span class="text-2xl font-bold text-foreground lg:text-2xl">Code<span class="text-primary">Craft</span></span>
        </div>
      </div>

      <!-- Lado Direito - Avatar com Popup -->
      <div class="relative" ref="userMenuContainer">
        <!-- User Avatar (clicável) -->
        <button 
          ref="userMenuButton"
          @click="toggleUserMenu"
          class="flex items-center space-x-3 rounded-lg p-2 hover:bg-white/5 transition-colors duration-200"
        >
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/20 text-primary">
            <span class="text-sm font-semibold">{{ userInitials }}</span>
          </div>
          <span class="hidden text-sm font-medium text-foreground lg:block">{{ userName }}</span>
        </button>

        <!-- Popup Menu com animação clean -->
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
            class="absolute right-0 top-12 z-50 w-64 rounded-lg border border-border bg-card py-3 shadow-xl origin-top-right"
          >
            <!-- Perfil do Utilizador (versão maior) -->
            <div class="px-4 py-3 border-b border-white/5">
              <div class="flex items-center space-x-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/20 text-primary">
                  <span class="text-lg font-semibold">{{ userInitials }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-base font-medium text-foreground">{{ userName }}</span>
                  <span class="text-xs text-foreground/40">{{ userEmail || 'joao.dias@codecraft.com' }}</span>
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

            <!-- Logout (com linha separadora) -->
            <div class="border-t border-white/5 pt-1">
              <button
                @click="handleLogoutClick"
                class="flex w-full items-center space-x-3 px-4 py-2 text-sm text-red-400 transition-colors hover:bg-white/5 hover:text-red-300"
              >
                <LogOut :size="18" />
                <span>Logout</span>
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
import { Menu, PanelRightClose, PanelLeftClose, User, Settings, LogOut } from 'lucide-vue-next'

// Props
defineProps<{
  sidebarVisible: boolean
  userName?: string
  userInitials?: string
  userEmail?: string
}>()

// Emits
defineEmits<{
  (e: 'toggle-mobile-menu'): void
  (e: 'toggle-sidebar'): void
}>()

// User menu state
const userMenuOpen = ref(false)

// Refs para os elementos do menu
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

// Placeholder functions (apenas visuais, não funcionais)
const handleProfileClick = () => {
  console.log('Profile clicked')
  closeUserMenu()
}

const handleSettingsClick = () => {
  console.log('Settings clicked')
  closeUserMenu()
}

const handleLogoutClick = () => {
  console.log('Logout clicked')
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
/* Transições suaves */
button {
  transition: all 200ms cubic-bezier(0.4, 0, 0.2, 1);
}

/* Garantir que a origem da transformação é no topo direito */
.origin-top-right {
  transform-origin: top right;
}
</style>