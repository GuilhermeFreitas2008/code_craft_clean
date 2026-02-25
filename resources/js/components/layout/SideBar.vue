<template>
  <!-- Desktop Sidebar -->
  <aside 
    v-if="!isMobile"
    class="fixed left-0 top-16 pt-9 z-30 h-[calc(100vh-4rem)] w-64 flex-col border-r border-border bg-card p-6 lg:flex transition-all duration-300 ease-out"
    :class="sidebarVisible ? 'translate-x-0 opacity-100' : '-translate-x-full opacity-0'"
  >
    <nav class="flex flex-col space-y-2">
      <button
        v-for="item in menuItems"
        :key="item.name"
        class="flex items-center space-x-3 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200"
        :class="[
          item.active
            ? 'bg-primary/10 text-primary'
            : 'text-foreground/60 hover:bg-white/5 hover:text-foreground'
        ]"
        @click="$emit('menu-click', item.name)"
      >
        <component :is="getIcon(item.icon)" :size="20" />
        <span>{{ item.name }}</span>
      </button>
    </nav>
  </aside>

  <!-- Mobile Sidebar - OCUPA ECRÃ TODO -->
  <Teleport to="body">
    <!-- Overlay escuro -->
    <div 
      v-if="isMobile && isOpen"
      class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden transition-opacity duration-300"
      :class="isOpen ? 'opacity-100' : 'opacity-0'"
      @click="$emit('close')"
    ></div>
    
    <!-- Sidebar mobile -->
    <div 
      v-if="isMobile"
      class="fixed left-0 top-0 z-50 h-screen w-64 bg-card p-6 transition-all duration-300 ease-out lg:hidden shadow-xl"
      :class="isOpen ? 'translate-x-0 opacity-100' : '-translate-x-full opacity-0'"
    >
      <!-- Logo no mobile -->
      <div class="mb-6 flex items-center space-x-2 pb-4 border-b border-white/5">
        <img src="/images/Logo.svg" alt="CodeCraft" class="h-10 w-10" />
        <span class="text-xl font-bold text-foreground">Code<span class="text-primary">Craft</span></span>
      </div>

      <nav class="flex flex-col space-y-2">
        <button
          v-for="item in menuItems"
          :key="item.name"
          class="flex items-center space-x-3 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200"
          :class="[
            item.active
              ? 'bg-primary/10 text-primary'
              : 'text-foreground/60 hover:bg-white/5 hover:text-foreground'
          ]"
          @click="$emit('menu-click', item.name); $emit('close')"
        >
          <component :is="getIcon(item.icon)" :size="20" />
          <span>{{ item.name }}</span>
        </button>
      </nav>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { 
  LayoutGrid,
  PlayCircle,
  Bookmark,
  CheckCircle
} from 'lucide-vue-next'

// Mapa de ícones
const iconMap = {
  LayoutGrid,
  PlayCircle,
  Bookmark,
  CheckCircle
}

// Props
defineProps<{
  isOpen: boolean
  isMobile: boolean
  sidebarVisible: boolean
  menuItems: Array<{
    name: string
    icon: string
    active: boolean
  }>
}>()

// Emits
defineEmits<{
  (e: 'close'): void
  (e: 'menu-click', menuName: string): void
}>()

// Função para obter o componente do ícone
const getIcon = (iconName: string) => {
  // @ts-ignore
  return iconMap[iconName] || LayoutGrid
}
</script>