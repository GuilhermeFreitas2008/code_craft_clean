<!-- NavBar.vue -->
<template>
  <header class="sticky top-0 z-40 border-b border-border bg-background/80 backdrop-blur-xl">
    <div class="flex h-16 items-center justify-between px-4 lg:px-8">
      <!-- Lado Esquerdo - Botão Toggle, Logo e Mobile Menu -->
      <div class="flex items-center space-x-4">
        <!-- Botão Toggle Sidebar (apenas desktop) -->
        <button 
          @click="$emit('toggle-sidebar')"
          class="hidden rounded-lg p-2 text-foreground/60 transition-colors hover:bg-white/5 hover:text-foreground lg:block"
          :title="sidebarVisible ? 'Fechar menu' : 'Abrir menu'"
        >
          <!-- Ícone invertido: começa com PanelLeftClose (apontando esquerda) -->
          <component 
            :is="sidebarVisible ? PanelLeftClose : PanelRightClose" 
            :size="24" 
          />
        </button>

        <!-- Mobile Menu Toggle -->
        <button 
          @click="$emit('toggle-mobile-menu')"
          class="rounded-lg p-2 text-foreground/60 hover:bg-white/5 hover:text-foreground lg:hidden"
        >
          <Menu :size="24" />
        </button>
        
        <!-- Logo -->
        <div class="flex items-center space-x-3">
          <img src="/images/Logo.svg" alt="CodeCraft" class="h-10 w-10 lg:h-12 lg:w-12" />
          <span class="text-2xl font-bold text-foreground lg:text-3xl">Code<span class="text-primary">Craft</span></span>
        </div>
      </div>

      <!-- Lado Direito -->
      <div class="flex items-center space-x-4">
        <!-- Theme Toggle -->
        <button 
          @click="$emit('toggle-theme')"
          class="rounded-lg p-2 text-foreground/60 transition-colors hover:bg-white/5 hover:text-foreground"
        >
          <component :is="isDark ? Sun : Moon" :size="20" />
        </button>

        <!-- User Avatar -->
        <button class="flex items-center space-x-3 rounded-lg p-2 hover:bg-white/5">
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/20 text-primary">
            <span class="text-sm font-semibold">{{ userInitials }}</span>
          </div>
          <span class="hidden text-sm font-medium text-foreground lg:block">{{ userName }}</span>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { Menu, Moon, Sun, PanelRightClose, PanelLeftClose } from 'lucide-vue-next'

// Props
defineProps<{
  isDark: boolean
  sidebarVisible: boolean
  userName?: string
  userInitials?: string
}>()

// Emits
defineEmits<{
  (e: 'toggle-mobile-menu'): void
  (e: 'toggle-theme'): void
  (e: 'toggle-sidebar'): void
}>()
</script>