<!-- resources/js/pages/logged/profile/ProfileDisplay.vue -->
<template>
  <div class="mx-auto max-w-4xl">
    <!-- Back Button -->
    <button 
      @click="goBack"
      class="mb-6 flex items-center space-x-2 text-foreground/60 hover:text-primary transition-colors group"
    >
      <ArrowLeft :size="20" class="group-hover:-translate-x-1 transition-transform" />
      <span>Back to Dashboard</span>
    </button>

    <!-- Profile Header with Tabs -->
    <div class="bg-gradient-to-br from-primary/5 to-transparent rounded-2xl p-8 border border-white/5">
      <div class="flex items-start justify-between">
        <div class="flex items-center space-x-6">
          <!-- Avatar -->
          <div class="relative">
            <div class="h-24 w-24 rounded-full bg-primary/20 flex items-center justify-center text-primary text-4xl font-bold">
              {{ userInitials }}
            </div>
          </div>

          <!-- Info -->
          <div>
            <h1 class="text-3xl font-bold text-foreground">{{ userStore.user?.name }}</h1>
            <p class="text-foreground/60 mt-1 flex items-center gap-2">
              <Mail :size="14" />
              {{ userStore.user?.email }}
            </p>
            <p class="text-foreground/40 text-sm mt-2 flex items-center gap-2">
              <Calendar :size="14" />
              Member since {{ formatMemberSince(userStore.user?.created_at) }}
            </p>
          </div>
        </div>

        <!-- Tabs -->
        <div class="flex space-x-2 bg-white/5 rounded-lg p-1">
          <button
            @click="$emit('tab-change', 'profile')"
            class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center space-x-2"
            :class="activeTab === 'profile' 
              ? 'bg-primary/20 text-primary' 
              : 'text-foreground/60 hover:text-foreground hover:bg-white/5'"
          >
            <User :size="16" />
            <span>Profile</span>
          </button>
          <button
            @click="$emit('tab-change', 'settings')"
            class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center space-x-2"
            :class="activeTab === 'settings' 
              ? 'bg-primary/20 text-primary' 
              : 'text-foreground/60 hover:text-foreground hover:bg-white/5'"
          >
            <Settings :size="16" />
            <span>Settings</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Stats Cards (agora com cores normais) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
      <!-- Courses Completed -->
      <div class="bg-card rounded-xl border border-white/5 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-foreground/40 text-sm">Courses Completed</p>
            <p class="text-3xl font-bold text-foreground mt-2">{{ completedCount }}</p>
          </div>
          <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
            <CheckCircle class="w-6 h-6 text-primary" />
          </div>
        </div>
      </div>

      <!-- In Progress -->
      <div class="bg-card rounded-xl border border-white/5 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-foreground/40 text-sm">In Progress</p>
            <p class="text-3xl font-bold text-foreground mt-2">{{ inProgressCount }}</p>
          </div>
          <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
            <PlayCircle class="w-6 h-6 text-primary" />
          </div>
        </div>
      </div>

      <!-- Watchlist Count -->
      <div class="bg-card rounded-xl border border-white/5 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-foreground/40 text-sm">Watchlist</p>
            <p class="text-3xl font-bold text-foreground mt-2">{{ watchlistCount }}</p>
          </div>
          <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
            <Bookmark class="w-6 h-6 text-primary" />
          </div>
        </div>
      </div>
    </div>

    <!-- Profile Tab Content -->
    <div v-if="activeTab === 'profile'" class="mt-8 space-y-8 animate-fade-in">
      <!-- Courses Section -->
      <div class="bg-card rounded-xl border border-white/5 p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-foreground">My Path</h2>
          
          <!-- Stats rápidas -->
          <div class="flex items-center space-x-4 text-sm">
            <div class="flex items-center space-x-2">
              <span class="w-2 h-2 rounded-full bg-primary"></span>
              <span class="text-foreground/60">In Progress: {{ inProgressCount }}</span>
            </div>
            <div class="flex items-center space-x-2">
              <span class="w-2 h-2 rounded-full bg-primary"></span>
              <span class="text-foreground/60">Completed: {{ completedCount }}</span>
            </div>
          </div>
        </div>
        
        <div v-if="activeCourses.length > 0" class="space-y-4">
          <div 
            v-for="course in activeCourses" 
            :key="course.id"
            class="flex items-center justify-between p-4 rounded-lg bg-white/5 border border-white/10 hover:border-primary/30 transition-all group cursor-pointer"
            @click="goToCourse(course.id)"
          >
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                <BookOpen :size="24" class="text-primary" />
              </div>
              <div>
                <h3 class="font-medium text-foreground">{{ course.title }}</h3>
                <p class="text-sm text-foreground/40">{{ getCategoryName(course.category) }}</p>
              </div>
            </div>

            <!-- Status e Progresso -->
            <div class="flex items-center space-x-4">
              <!-- Barra de progresso para cursos em andamento -->
              <div v-if="progressStore.hasProgress(course.id) && !progressStore.isCompleted(course.id)" class="w-32">
                <div class="flex justify-between text-xs mb-1">
                  <span class="text-foreground/40">Progress</span>
                  <span class="text-primary">{{ getCourseProgress(course.id) }}%</span>
                </div>
                <div class="h-1.5 bg-white/10 rounded-full overflow-hidden">
                  <div 
                    class="h-full bg-primary rounded-full transition-all duration-300"
                    :style="{ width: `${getCourseProgress(course.id)}%` }"
                  ></div>
                </div>
              </div>

              <!-- Badge Completed -->
              <div v-else-if="progressStore.isCompleted(course.id)" class="flex items-center space-x-2 text-primary">
                <CheckCircle :size="16" />
                <span class="text-sm font-medium">Completed</span>
              </div>

              <ChevronRight :size="18" class="text-foreground/20 group-hover:text-primary/50 transition-colors" />
            </div>
          </div>
        </div>
        
        <div v-else class="text-center py-12">
          <BookOpen :size="48" class="mx-auto text-primary/30 mb-4" />
          <h3 class="text-lg font-medium text-foreground mb-2">Your path begins here</h3>
          <p class="text-foreground/40">Start learning to track your progress!</p>
        </div>
      </div>
    </div>

    <!-- Settings Tab Content -->
    <div v-else class="mt-8 space-y-8 animate-fade-in">
      <!-- Account Settings -->
      <div class="bg-card rounded-xl border border-white/5 p-6">
        <h2 class="text-xl font-semibold text-foreground mb-6">Account Settings</h2>

        <form @submit.prevent="saveSettings" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-foreground/80 mb-2">Name</label>
            <input
              v-model="settings.name"
              type="text"
              class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 focus:border-primary focus:outline-none transition-colors text-foreground"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-foreground/80 mb-2">Email</label>
            <input
              v-model="settings.email"
              type="email"
              class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 focus:border-primary focus:outline-none transition-colors text-foreground"
            />
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors"
              :disabled="isSaving"
            >
              {{ isSaving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Preferences -->
      <div class="bg-card rounded-xl border border-white/5 p-6">
        <h2 class="text-xl font-semibold text-foreground mb-6">Preferences</h2>

        <div class="space-y-6">
          <!-- Language -->
          <div>
            <label class="block text-sm font-medium text-foreground/80 mb-2">Language</label>
            <select
              v-model="settings.language"
              class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 focus:border-primary focus:outline-none transition-colors text-foreground"
            >
              <option value="en">English</option>
              <option value="pt">Portuguese</option>
            </select>
          </div>

          <!-- Theme -->
          <div>
            <label class="block text-sm font-medium text-foreground/80 mb-2">Theme</label>
            <div class="flex space-x-4">
              <button
                @click="settings.theme = 'light'"
                class="flex-1 p-3 rounded-lg border-2 transition-all"
                :class="settings.theme === 'light' ? 'border-primary bg-primary/10' : 'border-white/10'"
              >
                <Sun :size="20" class="mx-auto mb-1 text-yellow-500" />
                <span class="text-sm">Light</span>
              </button>
              <button
                @click="settings.theme = 'dark'"
                class="flex-1 p-3 rounded-lg border-2 transition-all"
                :class="settings.theme === 'dark' ? 'border-primary bg-primary/10' : 'border-white/10'"
              >
                <Moon :size="20" class="mx-auto mb-1 text-blue-400" />
                <span class="text-sm">Dark</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="bg-card rounded-xl border border-white/5 p-6">
        <h2 class="text-xl font-semibold text-red-400 mb-6">Danger Zone</h2>
        <button
          @click="handleLogout"
          class="w-full px-6 py-3 bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 transition-colors flex items-center justify-center space-x-2"
        >
          <LogOut :size="18" />
          <span>Logout</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  ArrowLeft, Mail, Calendar, User, Settings, 
  CheckCircle, PlayCircle, BookOpen, ChevronRight,
  Sun, Moon, LogOut, Bookmark
} from 'lucide-vue-next'
import { useUserStore } from '@/stores/userStore'
import { useProgressStore } from '@/stores/progressStore'
import { useWatchlistStore } from '@/stores/watchlistStore'

// Types
interface Course {
  id: number
  title: string
  category?: any
}

interface Settings {
  name: string
  email: string
  language: string
  theme: string
}

const props = defineProps<{
  activeTab: 'profile' | 'settings'
  courses?: Course[] // 👈 RECEBER CURSOS DO PROFILEVIEW
}>()

const emit = defineEmits<{
  (e: 'tab-change', tab: 'profile' | 'settings'): void
}>()

const router = useRouter()
const userStore = useUserStore()
const progressStore = useProgressStore()
const watchlistStore = useWatchlistStore()

const isSaving = ref(false)
const settings = ref<Settings>({
  name: '',
  email: '',
  language: 'en',
  theme: 'dark'
})

// Usar cursos da prop em vez de mock
const courses = computed(() => props.courses || [])

// Apenas cursos em progresso ou completos
const activeCourses = computed(() => {
  return courses.value.filter((c: Course) => 
    progressStore.hasProgress(c.id) || progressStore.isCompleted(c.id)
  )
})

const userInitials = computed(() => {
  const name = userStore.user?.name
  if (!name) return 'U'
  return name.split(' ').map((n: string) => n[0]).join('').toUpperCase()
})

const completedCount = computed(() => progressStore.completedCourses.length)
const inProgressCount = computed(() => progressStore.coursesWithProgress.length)
const watchlistCount = computed(() => watchlistStore.items.length)

const formatMemberSince = (date: string | undefined): string => {
  if (!date) return 'Recently'
  
  try {
    const memberDate = new Date(date)
    return memberDate.toLocaleDateString('en-US', {
      month: 'long',
      year: 'numeric'
    })
  } catch (e) {
    return 'Recently'
  }
}

const getCourseProgress = (courseId: number): number => {
  // TODO: Implementar busca de progresso específico do curso
  // Por enquanto retorna valores mock baseados no ID
  if (courseId === 1) return 75
  if (courseId === 2) return 30
  return 0
}

const getCategoryName = (category: any): string => {
  if (!category) return 'Uncategorized'
  if (typeof category === 'object') return category.name || 'Uncategorized'
  return String(category)
}

const goBack = (): void => {
  router.push('/user')
}

const goToCourse = (courseId: number): void => {
  router.push(`/course/${courseId}`)
}

const saveSettings = async (): Promise<void> => {
  isSaving.value = true
  setTimeout(() => {
    isSaving.value = false
  }, 1000)
}

const handleLogout = async (): Promise<void> => {
  await userStore.logout()
  router.push('/login')
}

onMounted(() => {
  settings.value.name = userStore.user?.name || ''
  settings.value.email = userStore.user?.email || ''
})
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fadeIn 0.3s ease-out;
}
</style>