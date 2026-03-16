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
          <!-- Skeleton Loader enquanto carrega -->
          <div v-if="isLoading" class="space-y-6">
            <div class="h-10 w-3/4 animate-pulse rounded bg-white/5"></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div v-for="n in 3" :key="n" class="h-32 animate-pulse rounded-xl bg-white/5"></div>
            </div>
          </div>

          <!-- Profile Display -->
          <ProfileDisplay 
            v-else
            :active-tab="activeTab"
            :courses="courses"
            @tab-change="activeTab = $event"
          />
        </main>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import NavBar from '@/components/layout/NavBar.vue'
import SideBar from '@/components/layout/SideBar.vue'
import ProfileDisplay from './ProfileDisplay.vue'
import { useUserStore } from '@/stores/userStore'
import { useUiStore } from '@/stores/uiStore'
import { useProgressStore } from '@/stores/progressStore'
import { useWatchlistStore } from '@/stores/watchlistStore'
import api from '@/services/axios'

// Types
interface Course {
  id: number
  title: string
  category?: {
    id: number
    name: string
  } | string | null
  difficulty?: {
    id: number
    name: string
  } | string | null
  modules?: any[]
}

const userStore = useUserStore()
const uiStore = useUiStore()
const progressStore = useProgressStore()
const watchlistStore = useWatchlistStore()

const activeTab = ref<'profile' | 'settings'>('profile')
const isMobile = ref(window.innerWidth < 1024)
const isLoading = ref(true)
const courses = ref<Course[]>([])

const handleMenuClick = (menuName: string) => {
  uiStore.setActiveMenu(menuName)
}

const handleResize = () => {
  isMobile.value = window.innerWidth < 1024
}

// 👉 CARREGAR CURSOS DA API
const fetchCourses = async () => {
  try {
    const response = await api.get('/courses')
    courses.value = response.data
    console.log('📚 Cursos carregados:', courses.value)
  } catch (error) {
    console.error('❌ Erro ao carregar cursos:', error)
  }
}

// 👉 CARREGAR TODOS OS DADOS
const loadData = async () => {
  isLoading.value = true
  try {
    await Promise.all([
      progressStore.fetchProgressCourses(),
      watchlistStore.fetchWatchlist(),
      fetchCourses()
    ])
    console.log('✅ Dados carregados no ProfileView')
  } catch (error) {
    console.error('❌ Erro ao carregar dados:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
  loadData()
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>