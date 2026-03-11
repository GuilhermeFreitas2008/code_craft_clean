<!-- UserPage.vue -->
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
          <!-- Header with Title and Filters -->
          <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-3xl font-bold tracking-tight text-foreground animate-fade-in-up lg:text-4xl">
              {{ pageTitle }}
            </h1>

            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center sm:space-x-4">
              <!-- Categories Dropdown -->
              <div class="relative">
                <button
                  @click.stop="toggleCategoryDropdown"
                  class="flex items-center space-x-2 text-sm font-medium text-foreground/80 transition-colors hover:text-primary"
                >
                  <span>{{ filterStore.selectedCategoriesDisplay() || 'All Categories' }}</span>
                  <ChevronDown 
                    :size="16" 
                    class="transition-transform duration-300"
                    :class="{ 'rotate-180': categoryDropdownOpen }"
                  />
                </button>

                <div
                  v-if="categoryDropdownOpen"
                  class="absolute right-0 top-8 z-50 min-w-[220px] rounded-lg border border-border bg-card py-2 shadow-lg animate-fade-in"
                >
                  <div class="max-h-60 overflow-y-auto dropdown-scrollbar">
                    <div
                      v-for="cat in filterStore.availableCategories"
                      :key="cat"
                      class="px-4 py-2 hover:bg-white/5 transition-colors duration-150"
                    >
                      <label class="flex cursor-pointer items-center space-x-3">
                        <div class="relative flex items-center justify-center">
                          <input
                            type="checkbox"
                            :value="cat"
                            :checked="filterStore.selectedCategories.some(c => c.toLowerCase() === cat.toLowerCase())"
                            @change="filterStore.toggleCategory(cat)"
                            class="checkbox-hidden"
                          />
                          <div 
                            class="checkbox-custom"
                            :class="{ 'checkbox-checked': filterStore.selectedCategories.some(c => c.toLowerCase() === cat.toLowerCase()) }"
                          >
                            <Check 
                              v-if="filterStore.selectedCategories.some(c => c.toLowerCase() === cat.toLowerCase())"
                              :size="14" 
                              class="text-white"
                            />
                          </div>
                        </div>
                        <span class="text-sm" :class="filterStore.selectedCategories.some(c => c.toLowerCase() === cat.toLowerCase()) ? 'text-primary' : 'text-foreground/80'">
                          {{ cat }}
                        </span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Difficulties Dropdown -->
              <div class="relative">
                <button
                  @click.stop="toggleDifficultyDropdown"
                  class="flex items-center space-x-2 text-sm font-medium text-foreground/80 transition-colors hover:text-primary"
                >
                  <span>{{ filterStore.selectedDifficultiesDisplay() || 'All Difficulties' }}</span>
                  <ChevronDown 
                    :size="16" 
                    class="transition-transform duration-300"
                    :class="{ 'rotate-180': difficultyDropdownOpen }"
                  />
                </button>

                <div
                  v-if="difficultyDropdownOpen"
                  class="absolute right-0 top-8 z-50 min-w-[220px] rounded-lg border border-border bg-card py-2 shadow-lg animate-fade-in"
                >
                  <div class="max-h-60 overflow-y-auto dropdown-scrollbar">
                    <div
                      v-for="diff in filterStore.availableDifficulties"
                      :key="diff"
                      class="px-4 py-2 hover:bg-white/5 transition-colors duration-150"
                    >
                      <label class="flex cursor-pointer items-center space-x-3">
                        <div class="relative flex items-center justify-center">
                          <input
                            type="checkbox"
                            :value="diff"
                            :checked="filterStore.selectedDifficulties.some(d => d.toLowerCase() === diff.toLowerCase())"
                            @change="filterStore.toggleDifficulty(diff)"
                            class="checkbox-hidden"
                          />
                          <div 
                            class="checkbox-custom"
                            :class="{ 'checkbox-checked': filterStore.selectedDifficulties.some(d => d.toLowerCase() === diff.toLowerCase()) }"
                          >
                            <Check 
                              v-if="filterStore.selectedDifficulties.some(d => d.toLowerCase() === diff.toLowerCase())"
                              :size="14" 
                              class="text-white"
                            />
                          </div>
                        </div>
                        <span class="text-sm capitalize" :class="filterStore.selectedDifficulties.some(d => d.toLowerCase() === diff.toLowerCase()) ? 'text-primary' : 'text-foreground/80'">
                          {{ diff }}
                        </span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Skeleton Loader -->
          <div v-if="isLoading" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div
              v-for="n in 8"
              :key="n"
              class="animate-pulse rounded-xl border border-white/5 bg-card p-6"
            >
              <div class="mb-4 flex justify-start">
                <div class="h-16 w-16 rounded-2xl bg-white/5"></div>
              </div>
              <div class="mb-2 h-6 w-3/4 rounded bg-white/5"></div>
              <div class="my-4 border-t border-white/5"></div>
              <div class="flex flex-col gap-2">
                <div class="h-4 w-20 rounded bg-white/5"></div>
                <div class="h-4 w-24 rounded bg-white/5"></div>
                <div class="h-4 w-16 rounded bg-white/5"></div>
              </div>
            </div>
          </div>

          <!-- Courses Grid -->
          <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <router-link
              v-for="course in filteredCourses"
              :key="course.id"
              :to="`/course/${course.id}`"
              class="block"
            >
              <div class="group relative flex flex-col overflow-hidden rounded-xl border border-white/5 bg-card p-6 transition-all duration-300 ease-out hover:-translate-y-1 hover:border-primary/20 hover:shadow-lg hover:shadow-primary/5">
                <!-- Technology Icon -->
                <div class="mb-4 flex justify-start">
                  <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                    <component :is="getCourseIcon(getCategoryName(course.category))" :size="32" />
                  </div>
                </div>

                <!-- Title -->
                <div class="mb-2">
                  <h3 class="text-left text-lg font-semibold text-foreground">
                    {{ course.title }}
                  </h3>
                </div>

                <!-- Separator -->
                <div class="my-4 border-t border-white/5"></div>

                <!-- Metadata -->
                <div class="flex flex-col items-start gap-2 text-xs">
                  <!-- Lessons -->
                  <div class="flex items-center space-x-1 text-foreground/60">
                    <Film :size="14" />
                    <span>{{ getTotalLessons(course) }} {{ getTotalLessons(course) === 1 ? 'lesson' : 'lessons' }}</span>
                  </div>
                  
                  <!-- Difficulty -->
                  <div class="flex items-center space-x-1 text-foreground/60">
                    <component :is="getDifficultyIcon(getDifficultyName(course.difficulty))" :size="14" />
                    <span class="capitalize">{{ getDifficultyName(course.difficulty) }}</span>
                  </div>
                  
                  <!-- Category -->
                  <div class="flex items-center space-x-1 text-foreground/60">
                    <Tag :size="14" />
                    <span>{{ getCategoryName(course.category) }}</span>
                  </div>
                </div>

                <!-- Hover Effect -->
                <div class="absolute inset-0 rounded-xl bg-gradient-to-t from-primary/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
              </div>
            </router-link>
          </div>

          <div v-if="!isLoading && filteredCourses.length === 0" class="mt-12 text-center">
            <p class="text-foreground/60">No courses found with the selected filters.</p>
          </div>
        </main>
      </div>
    </div>

    <div 
      v-if="categoryDropdownOpen || difficultyDropdownOpen"
      class="fixed inset-0 z-40"
      @click="closeAllDropdowns"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/axios'
import { 
  Film,
  Tag,
  Layers,
  Database,
  Code2,
  Coffee,
  Terminal,
  Binary,
  Cpu,
  ChevronDown,
  Check,
  Smartphone,
  Palette,
  Cloud,
  Award,
  TrendingUp,
  Sparkles
} from 'lucide-vue-next'

import { useUserStore } from '@/stores/userStore'
import { useUiStore } from '@/stores/uiStore'
import { useFilterStore } from '@/stores/filterStore'
import { useWatchlistStore } from '@/stores/watchlistStore'
import { useProgressStore } from '@/stores/progressStore'

import NavBar from '@/components/layout/NavBar.vue'
import SideBar from '@/components/layout/SideBar.vue'

// Stores
const userStore = useUserStore()
const uiStore = useUiStore()
const filterStore = useFilterStore()
const watchlistStore = useWatchlistStore()
const progressStore = useProgressStore()

const router = useRouter()

// Types
interface Category {
  id: number
  name: string
}

interface Difficulty {
  id: number
  name: string
}

interface Module {
  id: number
  title: string
  lessons?: any[]
}

interface Course {
  id: number
  title: string
  description?: string
  category?: Category | string | null
  difficulty?: Difficulty | string | null
  technology?: string
  modules?: Module[]
}

// Loading state
const isLoading = ref(false)
const initialLoadComplete = ref(false)

// Mobile detection
const isMobile = ref(window.innerWidth < 1024)

// Dropdown states
const categoryDropdownOpen = ref(false)
const difficultyDropdownOpen = ref(false)

// Courses Data
const courses = ref<Course[]>([])

// Cache key
const CACHE_KEY = 'cached_courses'
const CACHE_TIMESTAMP_KEY = 'cached_courses_timestamp'
const CACHE_DURATION = 5 * 60 * 1000 // 5 minutos

// ================================================
// TÍTULO DINÂMICO CONFORME MENU ATIVO
// ================================================
const pageTitle = computed(() => {
  switch (uiStore.activeMenuItem) {
    case 'Watchlist':
      return 'My Watchlist'
    case 'Continue':
      return 'Continue Learning'
    case 'Completed':
      return 'Completed Courses'
    default:
      return 'All Series'
  }
})

// FUNÇÕES AUXILIARES PARA EXTRAIR NOMES
const getCategoryName = (category: Category | string | null | undefined): string => {
  if (!category) return 'Uncategorized'
  if (typeof category === 'object' && category !== null) {
    return category.name || 'Uncategorized'
  }
  return String(category)
}

const getDifficultyName = (difficulty: Difficulty | string | null | undefined): string => {
  if (!difficulty) return 'unknown'
  if (typeof difficulty === 'object' && difficulty !== null) {
    return difficulty.name || 'unknown'
  }
  return String(difficulty)
}

// FUNÇÃO PARA CONTAR LIÇÕES ATRAVÉS DOS MÓDULOS
const getTotalLessons = (course: Course): number => {
  if (!course.modules || !Array.isArray(course.modules)) {
    return 0
  }
  
  return course.modules.reduce((total, module) => {
    return total + (module.lessons?.length || 0)
  }, 0)
}

// Função para carregar do cache
const loadFromCache = () => {
  try {
    const cached = localStorage.getItem(CACHE_KEY)
    const timestamp = localStorage.getItem(CACHE_TIMESTAMP_KEY)
    
    if (cached && timestamp) {
      const age = Date.now() - parseInt(timestamp)
      if (age < CACHE_DURATION) {
        courses.value = JSON.parse(cached)
        return true
      }
    }
  } catch (error) {
    console.error('Erro ao ler cache:', error)
  }
  return false
}

// Função para guardar no cache
const saveToCache = (data: Course[]) => {
  try {
    localStorage.setItem(CACHE_KEY, JSON.stringify(data))
    localStorage.setItem(CACHE_TIMESTAMP_KEY, Date.now().toString())
  } catch (error) {
    console.error('Erro ao guardar cache:', error)
  }
}

// ================================================
// FILTERED COURSES - COM FILTRO POR MENU
// ================================================
const filteredCourses = computed(() => {
  let filtered = courses.value

  // 1. Filtrar por categoria
  if (filterStore.selectedCategories.length > 0) {
    filtered = filtered.filter(course => {
      const courseCategory = getCategoryName(course.category)
      return filterStore.selectedCategories.some(
        cat => cat.toLowerCase() === courseCategory.toLowerCase()
      )
    })
  }

  // 2. Filtrar por dificuldade
  if (filterStore.selectedDifficulties.length > 0) {
    filtered = filtered.filter(course => {
      const courseDifficulty = getDifficultyName(course.difficulty)
      return filterStore.selectedDifficulties.some(
        diff => diff.toLowerCase() === courseDifficulty.toLowerCase()
      )
    })
  }

  // 3. Filtrar por menu ativo
  switch (uiStore.activeMenuItem) {
    case 'Watchlist':
      filtered = filtered.filter(course => watchlistStore.isInWatchlist(course.id))
      break
    case 'Continue':
      filtered = filtered.filter(course => progressStore.hasProgress(course.id))
      break
    case 'Completed':
      filtered = filtered.filter(course => progressStore.isCompleted(course.id))
      break
  }

  return filtered
})

// Dropdown Functions
const toggleCategoryDropdown = () => {
  categoryDropdownOpen.value = !categoryDropdownOpen.value
  if (categoryDropdownOpen.value) difficultyDropdownOpen.value = false
}

const toggleDifficultyDropdown = () => {
  difficultyDropdownOpen.value = !difficultyDropdownOpen.value
  if (difficultyDropdownOpen.value) categoryDropdownOpen.value = false
}

const closeAllDropdowns = () => {
  categoryDropdownOpen.value = false
  difficultyDropdownOpen.value = false
}

// Menu Functions
const handleMenuClick = (menuName: string) => {
  uiStore.setActiveMenu(menuName)
  // Não redireciona, apenas atualiza o filtro
}

// Difficulty Helper
const getDifficultyIcon = (difficulty: string) => {
  switch(difficulty?.toLowerCase()) {
    case 'beginner': return Award
    case 'intermediate': return TrendingUp
    case 'advanced': return Sparkles
    default: return Award
  }
}

// Course Icon Helper
const getCourseIcon = (category: string) => {
  switch(category?.toLowerCase()) {
    case 'back end':
    case 'backend':
      return Terminal
    case 'front end':
    case 'frontend':
      return Code2
    case 'data science':
      return Database
    case 'mobile':
      return Smartphone
    case 'devops':
      return Cloud
    case 'ui/ux design':
      return Palette
    default:
      return Layers
  }
}

// Click outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (!target.closest('.relative')) closeAllDropdowns()
}

// Window resize
const handleResize = () => {
  isMobile.value = window.innerWidth < 1024
  if (!isMobile.value) uiStore.closeMobileMenu()
}

// Lifecycle
onMounted(async () => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('resize', handleResize)

  const hasCache = loadFromCache()
  
  try {
    isLoading.value = !hasCache
    
    // Buscar filtros e cursos em paralelo
    const [filtersResult, coursesResult] = await Promise.all([
      filterStore.fetchFilters(),
      api.get('/courses')
    ])
    
    courses.value = coursesResult.data
    saveToCache(coursesResult.data)
    
    // Se estiver autenticado, carregar watchlist e progresso
    if (userStore.isAuthenticated()) {
      await Promise.all([
        watchlistStore.fetchWatchlist(),
        progressStore.fetchProgressCourses()
      ])
    }
    
  } catch (error) {
    console.error('❌ Erro ao carregar dados:', error)
  } finally {
    isLoading.value = false
    initialLoadComplete.value = true
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
/* Transições específicas da página */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Animação suave para o conteúdo principal */
.flex-1 {
  transition: margin-left 300ms cubic-bezier(0.4, 0, 0.2, 1);
}

/* Hover effects mais subtis */
.group {
  transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
}

.group:hover {
  transform: translateY(-2px);
}

/* Focus styles */
*:focus-visible {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
}

/* Checkbox personalizado (específico desta página) */
.checkbox-hidden {
  position: absolute;
  width: 0;
  height: 0;
  opacity: 0;
  margin: 0;
  padding: 0;
}

.checkbox-custom {
  display: flex;
  height: 1.25rem;
  width: 1.25rem;
  align-items: center;
  justify-content: center;
  border-radius: 0.375rem;
  border-width: 2px;
  border-color: var(--color-border);
  background-color: rgba(15, 23, 42, 0.5);
  transition: all 200ms cubic-bezier(0.4, 0, 0.2, 1);
}

.checkbox-custom:hover {
  border-color: var(--color-primary);
  opacity: 0.5;
}

.checkbox-checked {
  border-color: var(--color-primary);
  background-color: var(--color-primary);
}

/* Animação de fade-in para o dropdown (específica) */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fadeIn 200ms cubic-bezier(0.4, 0, 0.2, 1);
}
</style>