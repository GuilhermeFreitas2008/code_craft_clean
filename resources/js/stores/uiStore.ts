import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useUiStore = defineStore('ui', () => {
  // Estado da sidebar
  const sidebarVisible = ref(true)
  const mobileMenuOpen = ref(false)
  
  // Estado do menu ativo (pode ser null)
  const activeMenuName = ref<string | null>('All Series')
  
  // Menu items (o active é derivado do activeMenuName)
  const menuItems = computed(() => [
    { name: 'All Series', icon: 'LayoutGrid', active: activeMenuName.value === 'All Series' },
    { name: 'Continue', icon: 'PlayCircle', active: activeMenuName.value === 'Continue' },
    { name: 'Watchlist', icon: 'Bookmark', active: activeMenuName.value === 'Watchlist' },
    { name: 'Completed', icon: 'CheckCircle', active: activeMenuName.value === 'Completed' }
  ])

  // Getter (mantém para compatibilidade)
  const activeMenuItem = computed(() => activeMenuName.value)

  // Actions
  const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value
  }

  const setActiveMenu = (menuName: string | null) => {
    activeMenuName.value = menuName
  }

  const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value
  }

  const closeMobileMenu = () => {
    mobileMenuOpen.value = false
  }

  return {
    sidebarVisible,
    mobileMenuOpen,
    menuItems,
    activeMenuItem,
    activeMenuName,
    toggleSidebar,
    setActiveMenu,
    toggleMobileMenu,
    closeMobileMenu
  }
}, {
  persist: {
    key: 'codecraft-ui',
    storage: localStorage,
    pick: ['sidebarVisible']
  }
})