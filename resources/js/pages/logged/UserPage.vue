<!-- UserPage.vue -->
<template>
  <div class="flex min-h-screen flex-col bg-background">
    <!-- Navbar -->
    <NavBar 
      :is-dark="isDark"
      :sidebar-visible="sidebarVisible"
      user-name="João Dias"
      user-initials="JD"
      @toggle-mobile-menu="mobileMenuOpen = !mobileMenuOpen"
      @toggle-theme="toggleTheme"
      @toggle-sidebar="toggleSidebar"
    />

    <div class="flex flex-1">
      <!-- Sidebar -->
      <SideBar 
        :is-open="mobileMenuOpen"
        :is-mobile="isMobile"
        :sidebar-visible="sidebarVisible"
        :menu-items="menuItems"
        @close="mobileMenuOpen = false"
        @menu-click="setActiveMenu"
      />

      <!-- Conteúdo Principal - Ajusta a margem conforme sidebar -->
      <div 
        class="flex-1 transition-all duration-300"
        :class="sidebarVisible ? 'lg:ml-64' : 'lg:ml-0'"
      >
        <main class="p-4 lg:p-8">
          <!-- Header com Título e Filtros -->
          <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-3xl font-bold tracking-tight text-foreground animate-fade-in-up lg:text-4xl">
              Todas as Séries
            </h1>

            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center sm:space-x-4">
              <!-- Dropdown Categorias -->
              <div class="relative">
                <button
                  @click.stop="toggleCategoryDropdown"
                  class="flex items-center space-x-2 text-sm font-medium text-foreground/80 transition-colors hover:text-primary"
                >
                  <span>{{ selectedCategory || 'Todas as Categorias' }}</span>
                  <ChevronDown 
                    :size="16" 
                    class="transition-transform duration-300"
                    :class="{ 'rotate-180': categoryDropdownOpen }"
                  />
                </button>

                <div
                  v-if="categoryDropdownOpen"
                  class="absolute right-0 top-8 z-50 min-w-[180px] rounded-lg border border-border bg-card py-2 shadow-lg"
                >
                  <button
                    v-for="cat in categories"
                    :key="cat"
                    @click="selectCategory(cat)"
                    class="w-full px-4 py-2 text-left text-sm transition-colors hover:bg-white/5"
                    :class="selectedCategory === cat ? 'text-primary' : 'text-foreground/80'"
                  >
                    {{ cat }}
                  </button>
                </div>
              </div>

              <!-- Dropdown Tópicos -->
              <div class="relative">
                <button
                  @click.stop="toggleTopicDropdown"
                  class="flex items-center space-x-2 text-sm font-medium text-foreground/80 transition-colors hover:text-primary"
                >
                  <span>{{ selectedTopic || 'Todos os Tópicos' }}</span>
                  <ChevronDown 
                    :size="16" 
                    class="transition-transform duration-300"
                    :class="{ 'rotate-180': topicDropdownOpen }"
                  />
                </button>

                <div
                  v-if="topicDropdownOpen"
                  class="absolute right-0 top-8 z-50 min-w-[180px] rounded-lg border border-border bg-card py-2 shadow-lg"
                >
                  <button
                    v-for="topic in topics"
                    :key="topic"
                    @click="selectTopic(topic)"
                    class="w-full px-4 py-2 text-left text-sm transition-colors hover:bg-white/5"
                    :class="selectedTopic === topic ? 'text-primary' : 'text-foreground/80'"
                  >
                    {{ topic }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Grid de Cursos -->
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div
              v-for="course in filteredCourses"
              :key="course.id"
              class="group relative overflow-hidden rounded-xl border border-white/5 bg-card p-6 transition-all duration-300 hover:-translate-y-1 hover:border-primary/20 hover:shadow-lg hover:shadow-primary/5"
            >
              <div class="mb-4 flex justify-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                  <component :is="course.icon" :size="40" />
                </div>
              </div>

              <h3 class="mb-1 text-center text-lg font-semibold text-foreground">
                {{ course.title }}
              </h3>

              <p class="mb-4 text-center text-sm text-foreground/60">
                Com {{ course.instructor }}
              </p>

              <div class="flex flex-wrap items-center justify-center gap-3 border-t border-white/5 pt-4 text-xs">
                <div class="flex items-center space-x-1 text-foreground/60">
                  <Film :size="14" />
                  <span>{{ course.episodes }} eps</span>
                </div>
                <div class="flex items-center space-x-1 text-foreground/60">
                  <component 
                    :is="getLevelIcon(course.level)" 
                    :size="14"
                    :class="getLevelColor(course.level)"
                  />
                  <span>{{ course.level }}</span>
                </div>
                <div class="flex items-center space-x-1 text-foreground/60">
                  <Tag :size="14" />
                  <span>{{ course.category }}</span>
                </div>
              </div>

              <div class="absolute inset-0 rounded-xl bg-gradient-to-t from-primary/5 to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>
            </div>
          </div>

          <div v-if="filteredCourses.length === 0" class="mt-12 text-center">
            <p class="text-foreground/60">Nenhum curso encontrado com os filtros selecionados.</p>
          </div>
        </main>
      </div>
    </div>

    <div 
      v-if="categoryDropdownOpen || topicDropdownOpen"
      class="fixed inset-0 z-40"
      @click="closeAllDropdowns"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { 
  Film,
  Tag,
  Award,
  TrendingUp,
  Layers,
  Database,
  Code2,
  Coffee,
  Terminal,
  Binary,
  Cpu,
  ChevronDown
} from 'lucide-vue-next'

import NavBar from '@/components/layout/NavBar.vue'
import SideBar from '@/components/layout/SideBar.vue'

// Types
interface Course {
  id: number
  title: string
  instructor: string
  level: string
  category: string
  episodes: number
  icon: any
}

// Menu Items
const menuItems = ref([
  { name: 'All Series', icon: 'LayoutGrid', active: true },
  { name: 'Continue', icon: 'PlayCircle', active: false },
  { name: 'Watchlist', icon: 'Bookmark', active: false },
  { name: 'Completos', icon: 'CheckCircle', active: false }
])

// Mobile detection
const isMobile = ref(window.innerWidth < 1024)
const mobileMenuOpen = ref(false)

// Sidebar visibility state
const sidebarVisible = ref(true)

// Theme
const isDark = ref(true)

// Filter States
const selectedCategory = ref('')
const selectedTopic = ref('')
const categoryDropdownOpen = ref(false)
const topicDropdownOpen = ref(false)

// Filter Options
const categories = ['Back-end', 'Front-end', 'Base de Dados', 'Frameworks', 'Técnicas']
const topics = ['Iniciante', 'Intermédio', 'Avançado']

// Courses Data
const courses = ref<Course[]>([
  { id: 1, title: 'Python para Iniciantes', instructor: 'Carlos Silva', level: 'Iniciante', category: 'Back-end', episodes: 24, icon: Terminal },
  { id: 2, title: 'SQL Avançado e Otimização', instructor: 'Ana Martins', level: 'Avançado', category: 'Base de Dados', episodes: 18, icon: Database },
  { id: 3, title: 'JavaScript Moderno ES6+', instructor: 'Rui Santos', level: 'Intermédio', category: 'Front-end', episodes: 32, icon: Code2 },
  { id: 4, title: 'React do Zero ao Deploy', instructor: 'Marta Ferreira', level: 'Intermédio', category: 'Frameworks', episodes: 28, icon: Layers },
  { id: 5, title: 'C# e .NET Core', instructor: 'Pedro Costa', level: 'Intermédio', category: 'Back-end', episodes: 22, icon: Coffee },
  { id: 6, title: 'Java: POO e Design Patterns', instructor: 'Joana Pereira', level: 'Avançado', category: 'Back-end', episodes: 26, icon: Coffee },
  { id: 7, title: 'TypeScript na Prática', instructor: 'Diogo Nunes', level: 'Intermédio', category: 'Front-end', episodes: 20, icon: Binary },
  { id: 8, title: 'Automação com Python', instructor: 'Sofia Almeida', level: 'Iniciante', category: 'Técnicas', episodes: 16, icon: Cpu }
])

// Computed
const filteredCourses = computed(() => {
  return courses.value.filter(course => {
    const matchesCategory = !selectedCategory.value || course.category === selectedCategory.value
    const matchesTopic = !selectedTopic.value || course.level === selectedTopic.value
    return matchesCategory && matchesTopic
  })
})

// Dropdown Functions
const toggleCategoryDropdown = () => {
  categoryDropdownOpen.value = !categoryDropdownOpen.value
  topicDropdownOpen.value = false
}

const toggleTopicDropdown = () => {
  topicDropdownOpen.value = !topicDropdownOpen.value
  categoryDropdownOpen.value = false
}

const selectCategory = (category: string) => {
  selectedCategory.value = selectedCategory.value === category ? '' : category
  categoryDropdownOpen.value = false
}

const selectTopic = (topic: string) => {
  selectedTopic.value = selectedTopic.value === topic ? '' : topic
  topicDropdownOpen.value = false
}

const closeAllDropdowns = () => {
  categoryDropdownOpen.value = false
  topicDropdownOpen.value = false
}

// Menu Functions
const setActiveMenu = (menuName: string) => {
  menuItems.value.forEach(item => {
    item.active = item.name === menuName
  })
}

// Sidebar Toggle
const toggleSidebar = () => {
  sidebarVisible.value = !sidebarVisible.value
}

// Theme
const toggleTheme = () => {
  isDark.value = !isDark.value
}

// Level Helpers
const getLevelIcon = (level: string) => level === 'Intermédio' ? TrendingUp : Award
const getLevelColor = (level: string) => {
  const colors = { Iniciante: 'text-green-400', Intermédio: 'text-yellow-400', Avançado: 'text-red-400' }
  return colors[level as keyof typeof colors] || 'text-foreground/60'
}

// Click outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (!target.closest('.relative')) closeAllDropdowns()
}

// Window resize
const handleResize = () => {
  isMobile.value = window.innerWidth < 1024
  if (!isMobile.value) mobileMenuOpen.value = false
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
/* Smooth transitions */
button, .group {
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.rotate-180 {
  transform: rotate(180deg);
}

/* Focus styles */
*:focus-visible {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
}
</style>