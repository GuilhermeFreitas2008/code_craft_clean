import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useUiStore = defineStore('ui', () => {
  // Estado da sidebar
  const sidebarVisible = ref(true)
  const mobileMenuOpen = ref(false)
  
  // Menu items
  const menuItems = ref([
    { name: 'All Series', icon: 'LayoutGrid', active: true },
    { name: 'Continue', icon: 'PlayCircle', active: false },
    { name: 'Watchlist', icon: 'Bookmark', active: false },
    { name: 'Completed', icon: 'CheckCircle', active: false }
  ])

  // Getter
  const activeMenuItem = computed(() => {
    return menuItems.value.find(item => item.active)?.name || 'All Series'
  })

  // Actions
  const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value
  }

  const setActiveMenu = (menuName: string) => {
    menuItems.value.forEach(item => {
      item.active = item.name === menuName
    })
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