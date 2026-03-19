<!-- resources/js/pages/logged/profile/ProfileView.vue -->
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
          <!-- Skeleton Loader -->
          <div v-if="isLoading" class="space-y-6">
            <div class="h-10 w-3/4 animate-pulse rounded bg-white/5"></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div v-for="n in 3" :key="n" class="h-32 animate-pulse rounded-xl bg-white/5"></div>
            </div>
          </div>

          <!-- Profile Display -->
          <ProfileDisplay 
            v-else
            :courses="courses"
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
import ProfileDisplay from './ProfileDisplay.vue'
import { useUserStore } from '@/stores/userStore'
import { useUiStore } from '@/stores/uiStore'
import { useProgressStore } from '@/stores/progressStore'
import { useWatchlistStore } from '@/stores/watchlistStore'
import api from '@/services/axios'

const router = useRouter()
const userStore = useUserStore()
const uiStore = useUiStore()
const progressStore = useProgressStore()
const watchlistStore = useWatchlistStore()

const isMobile = ref(window.innerWidth < 1024)
const isLoading = ref(true)
const courses = ref<any[]>([])

const handleMenuClick = (menuName: string) => {
  uiStore.setActiveMenu(menuName)
  router.push('/user') // Navega para a página principal com o filtro
}

const handleResize = () => {
  isMobile.value = window.innerWidth < 1024
}

const fetchCourses = async () => {
  try {
    const response = await api.get('/courses')
    courses.value = response.data
  } catch (error) {
    console.error('Erro ao carregar cursos:', error)
  }
}

const loadData = async () => {
  isLoading.value = true
  try {
    await Promise.all([
      progressStore.fetchProgressCourses(),
      watchlistStore.fetchWatchlist(),
      fetchCourses()
    ])
  } catch (error) {
    console.error('Erro ao carregar dados:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
  loadData()
  
  // Limpar menu ativo quando entra na página de perfil
  uiStore.setActiveMenu(null)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>