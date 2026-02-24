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
              All Series
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
                  <div class="max-h-60 overflow-y-auto">
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
                            :checked="filterStore.selectedCategories.includes(cat)"
                            @change="filterStore.toggleCategory(cat)"
                            class="checkbox-hidden"
                          />
                          <div 
                            class="checkbox-custom"
                            :class="{ 'checkbox-checked': filterStore.selectedCategories.includes(cat) }"
                          >
                            <Check 
                              v-if="filterStore.selectedCategories.includes(cat)"
                              :size="14" 
                              class="text-white"
                            />
                          </div>
                        </div>
                        <span class="text-sm" :class="filterStore.selectedCategories.includes(cat) ? 'text-primary' : 'text-foreground/80'">
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
                  <div class="max-h-60 overflow-y-auto">
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
                            :checked="filterStore.selectedDifficulties.includes(diff)"
                            @change="filterStore.toggleDifficulty(diff)"
                            class="checkbox-hidden"
                          />
                          <div 
                            class="checkbox-custom"
                            :class="{ 'checkbox-checked': filterStore.selectedDifficulties.includes(diff) }"
                          >
                            <Check 
                              v-if="filterStore.selectedDifficulties.includes(diff)"
                              :size="14" 
                              class="text-white"
                            />
                          </div>
                        </div>
                        <span class="text-sm capitalize" :class="filterStore.selectedDifficulties.includes(diff) ? 'text-primary' : 'text-foreground/80'">
                          {{ diff }}
                        </span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="isLoading" class="flex justify-center items-center py-20">
            <svg 
              class="h-8 w-8 animate-spin text-primary" 
              xmlns="http://www.w3.org/2000/svg" 
              fill="none" 
              viewBox="0 0 24 24"
            >
              <circle 
                class="opacity-25" 
                cx="12" 
                cy="12" 
                r="10" 
                stroke="currentColor" 
                stroke-width="4"
              />
              <path 
                class="opacity-75" 
                fill="currentColor" 
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              />
            </svg>
          </div>

          <!-- Courses Grid -->
          <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div
              v-for="course in filteredCourses"
              :key="course.id"
              class="group relative flex flex-col overflow-hidden rounded-xl border border-white/5 bg-card p-6 transition-all duration-300 ease-out hover:-translate-y-1 hover:border-primary/20 hover:shadow-lg hover:shadow-primary/5"
            >
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

import NavBar from '@/components/layout/NavBar.vue'
import SideBar from '@/components/layout/SideBar.vue'

// Stores
const userStore = useUserStore()
const uiStore = useUiStore()
const filterStore = useFilterStore()

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

// Mobile detection
const isMobile = ref(window.innerWidth < 1024)

// Dropdown states (continuam locais porque são UI temporária)
const categoryDropdownOpen = ref(false)
const difficultyDropdownOpen = ref(false)

// Courses Data
const courses = ref<Course[]>([])

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

// Filtered Courses
const filteredCourses = computed(() => {
  return courses.value.filter(course => {
    const courseCategory = getCategoryName(course.category)
    const courseDifficulty = getDifficultyName(course.difficulty)
    
    const matchesCategory = filterStore.selectedCategories.length === 0 || 
      filterStore.selectedCategories.includes(courseCategory)
    
    const matchesDifficulty = filterStore.selectedDifficulties.length === 0 || 
      filterStore.selectedDifficulties.includes(courseDifficulty)
    
    return matchesCategory && matchesDifficulty
  })
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
  if (menuName === 'All Series') {
    // Já estamos na página
  }
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

  // Carregar cursos da API
  try {
    isLoading.value = true
    const response = await api.get('/courses')
    courses.value = response.data
    
  } catch (error) {
    console.error('❌ Erro ao carregar cursos:', error)
  } finally {
    isLoading.value = false
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
/* Smooth transitions - versão mais clean */
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

/* Rotação suave */
.rotate-180 {
  transform: rotate(180deg);
}

/* Focus styles */
*:focus-visible {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
}

/* Checkbox personalizado */
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

/* Animação de fade-in para o dropdown */
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

/* Scrollbar personalizada */
.max-h-60::-webkit-scrollbar {
  width: 6px;
}

.max-h-60::-webkit-scrollbar-track {
  background: transparent;
}

.max-h-60::-webkit-scrollbar-thumb {
  background: #334155;
  border-radius: 3px;
  transition: background 200ms;
}

.max-h-60::-webkit-scrollbar-thumb:hover {
  background: #475569;
}
</style>