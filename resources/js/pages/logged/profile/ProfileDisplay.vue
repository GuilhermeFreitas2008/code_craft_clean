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

    <!-- Profile Header com Tabs -->
    <ProfileHeader 
      :active-tab="'profile'"
      @tab-change="handleTabChange"
    />

    <!-- Stats Cards -->
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

    <!-- My Path Section -->
    <div class="mt-8 space-y-8 animate-fade-in">
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
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  ArrowLeft, CheckCircle, PlayCircle, BookOpen, ChevronRight, Bookmark
} from 'lucide-vue-next'
import ProfileHeader from '@/components/profile/ProfileHeader.vue'
import { useUserStore } from '@/stores/userStore'
import { useProgressStore } from '@/stores/progressStore'
import { useWatchlistStore } from '@/stores/watchlistStore'

// Types
interface Course {
  id: number
  title: string
  category?: any
}

// Props
const props = defineProps<{
  courses?: Course[]
}>()

const router = useRouter()
const userStore = useUserStore()
const progressStore = useProgressStore()
const watchlistStore = useWatchlistStore()

// Usar cursos da prop
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
  if (courseId === 1) return 75
  if (courseId === 2) return 30
  return 0
}

const getCategoryName = (category: any): string => {
  if (!category) return 'Uncategorized'
  if (typeof category === 'object') return category.name || 'Uncategorized'
  return String(category)
}

const handleTabChange = (tab: 'profile' | 'settings') => {
  if (tab === 'settings') {
    router.push('/settings')
  }
}

const goBack = (): void => {
  router.push('/user')
}

const goToCourse = (courseId: number): void => {
  router.push(`/course/${courseId}`)
}
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