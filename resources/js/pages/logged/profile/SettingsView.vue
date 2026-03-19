<!-- resources/js/pages/logged/profile/SettingsView.vue -->
<template>
  <div class="flex min-h-screen flex-col bg-background">
    <!-- Navbar -->
    <NavBar 
      :sidebar-visible="uiStore.sidebarVisible"
      :user-name="userStore.user?.name"
      :user-email="userStore.user?.email"
      :user-initials="userStore.user?.name?.charAt(0)"
      @toggle-mobile-menu="uiStore.toggleMobileMenu()"
      @toggle-sidebar="uiStore.toggleSidebar()"
    />

    <div class="flex flex-1">
      <!-- Sidebar -->
      <SideBar 
        :is-open="uiStore.mobileMenuOpen"
        :is-mobile="isMobile"
        :sidebar-visible="uiStore.sidebarVisible"
        :menu-items="uiStore.menuItems"
        @close="uiStore.closeMobileMenu()"
        @menu-click="handleMenuClick"
      />

      <!-- Main Content -->
      <div 
        class="flex-1 transition-all duration-300 ease-out"
        :class="uiStore.sidebarVisible ? 'lg:ml-64' : 'lg:ml-0'"
      >
        <main class="p-4 lg:p-8">
          <!-- Skeleton Loader enquanto carrega -->
          <div v-if="isLoading" class="space-y-6">
            <div class="h-10 w-3/4 animate-pulse rounded bg-white/5"></div>
            <div class="h-64 animate-pulse rounded-xl bg-white/5"></div>
          </div>

          <!-- Settings Display -->
          <SettingsDisplay 
            v-else
            @back="goBack"
          />
        </main>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import NavBar from '@/components/layout/NavBar.vue'
import SideBar from '@/components/layout/SideBar.vue'
import SettingsDisplay from './SettingsDisplay.vue'
import { useUserStore } from '@/stores/userStore'
import { useUiStore } from '@/stores/uiStore'

const router = useRouter()
const userStore = useUserStore()
const uiStore = useUiStore()

const isMobile = ref(window.innerWidth < 1024)
const isLoading = ref(false)

const handleMenuClick = (menuName: string) => {
  uiStore.setActiveMenu(menuName)
  router.push('/user') // Navega para a página principal com o filtro
}

const handleResize = () => {
  isMobile.value = window.innerWidth < 1024
}

const goBack = () => {
  router.push('/profile')
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
  
  // Limpar menu ativo quando entra na página de settings
  uiStore.setActiveMenu(null)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>