<template>
  <div class="flex min-h-screen flex-col bg-background">
    <!-- Navbar -->
    <NavBar 
      :sidebar-visible="sidebarVisible"
      user-name="João Dias"
      user-initials="JD"
      user-email="joao.dias@codecraft.com"
      @toggle-mobile-menu="mobileMenuOpen = !mobileMenuOpen"
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

      <!-- Main Content -->
      <div 
        class="flex-1 transition-all duration-300 ease-out"
        :class="sidebarVisible ? 'lg:ml-64' : 'lg:ml-0'"
      >
        <main class="p-4 lg:p-8">
          <!-- Header with Title and Filter -->
          <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-3xl font-bold tracking-tight text-foreground animate-fade-in-up lg:text-4xl">
              All Series
            </h1>

            <!-- Categories Dropdown com Checkboxes -->
            <div class="relative">
              <button
                @click.stop="toggleCategoryDropdown"
                class="flex items-center space-x-2 text-sm font-medium text-foreground/80 transition-colors hover:text-primary"
              >
                <span>{{ selectedCategoriesDisplay || 'All Categories' }}</span>
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
                <!-- Category checkboxes com estilo personalizado -->
                <div class="max-h-60 overflow-y-auto">
                  <div
                    v-for="cat in categories"
                    :key="cat"
                    class="px-4 py-2 hover:bg-white/5 transition-colors duration-150"
                  >
                    <label class="flex cursor-pointer items-center space-x-3">
                      <!-- Checkbox personalizado -->
                      <div class="relative flex items-center justify-center">
                        <input
                          type="checkbox"
                          :value="cat"
                          v-model="selectedCategories"
                          class="checkbox-hidden"
                        />
                        <div 
                          class="checkbox-custom"
                          :class="{ 'checkbox-checked': selectedCategories.includes(cat) }"
                        >
                          <Check 
                            v-if="selectedCategories.includes(cat)"
                            :size="14" 
                            class="text-white"
                          />
                        </div>
                      </div>
                      <span class="text-sm" :class="selectedCategories.includes(cat) ? 'text-primary' : 'text-foreground/80'">
                        {{ cat }}
                      </span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Courses Grid -->
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div
              v-for="course in filteredCourses"
              :key="course.id"
              class="group relative flex flex-col overflow-hidden rounded-xl border border-white/5 bg-card p-6 transition-all duration-300 ease-out hover:-translate-y-1 hover:border-primary/20 hover:shadow-lg hover:shadow-primary/5"
            >
              <!-- Technology Icon -->
              <div class="mb-4 flex justify-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                  <component :is="course.icon" :size="40" />
                </div>
              </div>

              <!-- Title -->
              <div class="flex min-h-[4rem] flex-col justify-center">
                <h3 class="text-center text-lg font-semibold text-foreground">
                  {{ course.title }}
                </h3>
              </div>

              <!-- Separator -->
              <div class="my-4 border-t border-white/5"></div>

              <!-- Metadata -->
              <div class="flex flex-col items-center gap-2 text-xs">
                <!-- Lessons -->
                <div class="flex items-center space-x-1 text-foreground/60">
                  <Film :size="14" />
                  <span>{{ course.lessons }} {{ course.lessons === 1 ? 'lesson' : 'lessons' }}</span>
                </div>
                
                <!-- Category -->
                <div class="flex items-center space-x-1 text-foreground/60">
                  <Tag :size="14" />
                  <span>{{ course.category }}</span>
                </div>
              </div>

              <!-- Hover Effect -->
              <div class="absolute inset-0 rounded-xl bg-gradient-to-t from-primary/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
            </div>
          </div>

          <div v-if="filteredCourses.length === 0" class="mt-12 text-center">
            <p class="text-foreground/60">No courses found with the selected categories.</p>
          </div>
        </main>
      </div>
    </div>

    <div 
      v-if="categoryDropdownOpen"
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
  Cloud
} from 'lucide-vue-next'

import NavBar from '@/components/layout/NavBar.vue'
import SideBar from '@/components/layout/SideBar.vue'

// Types
interface Course {
  id: number
  title: string
  category: string
  lessons: number
  icon: any
}

// Menu Items
const menuItems = ref([
  { name: 'All Series', icon: 'LayoutGrid', active: true },
  { name: 'Continue', icon: 'PlayCircle', active: false },
  { name: 'Watchlist', icon: 'Bookmark', active: false },
  { name: 'Completed', icon: 'CheckCircle', active: false }
])

// Mobile detection
const isMobile = ref(window.innerWidth < 1024)
const mobileMenuOpen = ref(false)

// Sidebar visibility state
const sidebarVisible = ref(true)

// Filter States
const selectedCategories = ref<string[]>([])
const categoryDropdownOpen = ref(false)

// Filter Options
const categories = ['Back end', 'Front end', 'DevOps', 'Mobile', 'UI/UX Design', 'Data Science']

// Courses Data
const courses = ref<Course[]>([
  { id: 1, title: 'Python for Beginners', category: 'Back end', lessons: 24, icon: Terminal },
  { id: 2, title: 'Advanced SQL & Optimization', category: 'Data Science', lessons: 18, icon: Database },
  { id: 3, title: 'Modern JavaScript ES6+', category: 'Front end', lessons: 32, icon: Code2 },
  { id: 4, title: 'React from Zero to Deploy', category: 'Front end', lessons: 28, icon: Layers },
  { id: 5, title: 'C# and .NET Core', category: 'Back end', lessons: 22, icon: Coffee },
  { id: 6, title: 'Kotlin for Android', category: 'Mobile', lessons: 26, icon: Smartphone },
  { id: 7, title: 'TypeScript in Practice', category: 'Front end', lessons: 20, icon: Binary },
  { id: 8, title: 'Docker & Kubernetes', category: 'DevOps', lessons: 16, icon: Cloud },
  { id: 9, title: 'Figma for Designers', category: 'UI/UX Design', lessons: 14, icon: Palette },
  { id: 10, title: 'SwiftUI Masterclass', category: 'Mobile', lessons: 30, icon: Smartphone },
  { id: 11, title: 'CI/CD Pipelines', category: 'DevOps', lessons: 12, icon: Cloud },
  { id: 12, title: 'Data Analysis with Python', category: 'Data Science', lessons: 24, icon: Database }
])

// Computed
const selectedCategoriesDisplay = computed(() => {
  if (selectedCategories.value.length === 0) return ''
  if (selectedCategories.value.length === 1) return selectedCategories.value[0]
  return `${selectedCategories.value.length} categories`
})

const filteredCourses = computed(() => {
  if (selectedCategories.value.length === 0) return courses.value
  return courses.value.filter(course => selectedCategories.value.includes(course.category))
})

// Dropdown Functions
const toggleCategoryDropdown = () => {
  categoryDropdownOpen.value = !categoryDropdownOpen.value
}

const closeAllDropdowns = () => {
  categoryDropdownOpen.value = false
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