<!-- SideBar.vue -->
<template>
  <!-- Desktop Sidebar -->
  <aside 
    v-if="!isMobile"
    v-show="sidebarVisible"
    class="fixed left-0 top-16 z-30 h-[calc(100vh-4rem)] w-64 flex-col border-r border-border bg-card p-6 lg:flex"
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

  <!-- Mobile Sidebar (Overlay) -->
  <Teleport to="body">
    <div 
      v-if="isMobile && isOpen"
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm lg:hidden"
      @click="$emit('close')"
    ></div>
    
    <div 
      v-if="isMobile"
      class="fixed left-0 top-16 z-50 h-[calc(100vh-4rem)] w-64 transform bg-card p-6 transition-transform duration-300 lg:hidden"
      :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
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
  sidebarVisible: boolean  // NOVA PROP
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