<!-- CourseDisplay.vue -->
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
          <div v-if="loading" class="mx-auto max-w-7xl">
            <!-- Back Button Skeleton -->
            <div class="mb-6">
              <div class="h-6 w-24 animate-pulse rounded bg-white/5"></div>
            </div>

            <!-- Badges Skeleton -->
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
              <div></div>
              <div class="flex items-center gap-3">
                <div class="h-8 w-20 animate-pulse rounded-xl bg-white/5"></div>
                <div class="h-8 w-24 animate-pulse rounded-xl bg-white/5"></div>
              </div>
            </div>

            <!-- Grid Skeletons -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
              <!-- Main Column Skeletons -->
              <div class="lg:col-span-2 space-y-6">
                <!-- Course Header Skeleton -->
                <div class="rounded-xl border border-white/5 bg-card p-6">
                  <div class="h-8 w-3/4 animate-pulse rounded bg-white/5 mb-4"></div>
                  <div class="h-4 w-full animate-pulse rounded bg-white/5 mb-2"></div>
                  <div class="h-4 w-5/6 animate-pulse rounded bg-white/5 mb-6"></div>
                  
                  <!-- Progress Bar Skeleton -->
                  <div class="mt-6">
                    <div class="flex items-center justify-between mb-2">
                      <div class="h-4 w-24 animate-pulse rounded bg-white/5"></div>
                      <div class="h-4 w-8 animate-pulse rounded bg-white/5"></div>
                    </div>
                    <div class="h-2 w-full animate-pulse rounded-full bg-white/5"></div>
                  </div>

                  <!-- Action Buttons Skeleton -->
                  <div class="mt-6 flex flex-wrap gap-4">
                    <div class="h-10 w-32 animate-pulse rounded-lg bg-white/5"></div>
                    <div class="h-10 w-40 animate-pulse rounded-lg bg-white/5"></div>
                  </div>
                </div>

                <!-- Course Content Skeleton -->
                <div class="rounded-xl border border-white/5 bg-card p-6">
                  <div class="h-6 w-40 animate-pulse rounded bg-white/5 mb-4"></div>
                  
                  <!-- Module Skeletons -->
                  <div class="space-y-3">
                    <div
                      v-for="n in 3"
                      :key="n"
                      class="animate-pulse rounded-lg border border-white/5 bg-card p-4"
                    >
                      <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                          <div class="h-5 w-32 rounded bg-white/5"></div>
                          <div class="h-4 w-12 rounded bg-white/5"></div>
                        </div>
                        <div class="h-5 w-5 rounded bg-white/5"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sidebar Skeletons -->
              <div class="space-y-5">
                <div class="rounded-xl border border-white/5 bg-card p-4">
                  <div class="h-6 w-20 animate-pulse rounded bg-white/5 mb-4"></div>
                  <div class="flex flex-wrap gap-2">
                    <div
                      v-for="n in 4"
                      :key="n"
                      class="h-8 w-16 animate-pulse rounded-full bg-white/5"
                    ></div>
                  </div>
                </div>

                <div class="rounded-xl border border-white/5 bg-card p-5">
                  <div class="h-6 w-24 animate-pulse rounded bg-white/5 mb-4"></div>
                  <div class="space-y-3">
                    <div class="flex items-center justify-between">
                      <div class="h-4 w-20 animate-pulse rounded bg-white/5"></div>
                      <div class="h-4 w-24 animate-pulse rounded bg-white/5"></div>
                    </div>
                    <div class="flex items-center justify-between">
                      <div class="h-4 w-20 animate-pulse rounded bg-white/5"></div>
                      <div class="h-4 w-16 animate-pulse rounded bg-white/5"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Conteúdo Real -->
          <div v-else-if="course" class="mx-auto max-w-7xl">
            <!-- Back Button with Course Info -->
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
              <button 
                @click="goBack" 
                class="flex items-center space-x-2 text-foreground/60 transition-colors hover:text-primary"
              >
                <ChevronLeft :size="20" />
                <span>Back to series</span>
              </button>

              <!-- Course Difficulty and Category with Simple Hover -->
              <div class="flex items-center gap-3 text-sm">
                <!-- Difficulty Badge com hover simples -->
                <div 
                  class="flex items-center space-x-1 rounded-xl bg-primary/10 px-3 py-1.5 text-primary transition-colors hover:bg-primary/20"
                >
                  <component :is="getDifficultyIcon(course.difficulty)" :size="16" />
                  <span class="capitalize">{{ course.difficulty }}</span>
                </div>
                
                <!-- Category Badge com hover simples -->
                <div 
                  class="flex items-center space-x-1 rounded-xl bg-primary/10 px-3 py-1.5 text-primary transition-colors hover:bg-primary/20"
                >
                  <Tag :size="16" />
                  <span>{{ course.category }}</span>
                </div>
              </div>
            </div>

            <!-- Course Content -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
              <!-- Main Column (Course Content) -->
              <div class="lg:col-span-2 space-y-6">
                <!-- Course Header -->
                <div class="rounded-xl border border-white/5 bg-card p-6">
                  <h1 class="text-3xl font-bold text-foreground lg:text-4xl">
                    {{ course.title }}
                  </h1>
                  <p class="mt-2 text-foreground/60">
                    {{ course.description }}
                  </p>

                  <!-- Progress Bar -->
                  <div class="mt-6">
                    <div class="flex items-center justify-between text-sm">
                      <span class="text-foreground/60">Course progress</span>
                      <span class="font-medium text-primary">{{ Math.round(course.progressPercentage) }}%</span>
                    </div>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-white/5">
                      <div 
                        class="h-full rounded-full bg-primary transition-all duration-300"
                        :style="{ width: `${course.progressPercentage}%` }"
                      ></div>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="mt-6 flex flex-wrap gap-4">
                    <button class="inline-flex items-center space-x-2 rounded-lg bg-primary px-6 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20">
                      <Play :size="18" />
                      <span>{{ course.progressPercentage === 0 ? 'Start Course' : 'Continue Course' }}</span>
                    </button>
                    
                    <!-- Watchlist Button -->
                    <button 
                      @click="toggleWatchlist"
                      @mouseenter="handleMouseEnter"
                      @mouseleave="handleMouseLeave"
                      :disabled="isTransitioning"
                      class="watchlist-btn relative inline-flex items-center overflow-hidden rounded-lg border px-6 py-3 text-sm font-semibold transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-70 w-[200px]"
                      :class="[buttonClasses]"
                    >
                      <component 
                        :is="watchlistIcon"
                        :size="18" 
                        class="transition-transform duration-200 flex-shrink-0 mr-2"
                        :class="{ 'scale-110': isAnimating }"
                      />
                      <span class="flex-1 text-left">{{ watchlistButtonText }}</span>
                      <span 
                        v-if="showRipple"
                        class="ripple absolute rounded-full bg-white/30"
                        :style="{
                          top: rippleY + 'px',
                          left: rippleX + 'px',
                          width: rippleSize + 'px',
                          height: rippleSize + 'px'
                        }"
                      ></span>
                    </button>
                  </div>
                </div>

                <!-- Course Content by Modules -->
                <div class="rounded-xl border border-white/5 bg-card p-6">
                  <h2 class="mb-4 text-xl font-semibold text-foreground">Course Content</h2>
                  
                  <!-- Modules Accordion -->
                  <div class="space-y-3">
                    <div
                      v-for="(module, moduleIndex) in course.modules"
                      :key="moduleIndex"
                      class="overflow-hidden rounded-lg border border-white/5"
                    >
                      <!-- Module Header (clicável) -->
                      <button
                        @click="toggleModule(moduleIndex)"
                        class="flex w-full items-center justify-between bg-card px-4 py-3 text-left transition-colors hover:bg-white/5"
                      >
                        <div class="flex items-center space-x-2">
                          <span class="text-sm font-medium text-foreground">{{ module.title }}</span>
                          <span class="text-xs text-foreground/40">({{ module.lessons?.length || 0 }} lessons)</span>
                        </div>
                        
                        <!-- Seta animada -->
                        <ChevronDown 
                          :size="18" 
                          class="text-foreground/60 transition-transform duration-300"
                          :class="{ 'rotate-180': openModules.includes(moduleIndex) }"
                        />
                      </button>

                      <!-- Module Lessons (com animação de altura) -->
                      <Transition
                        @before-enter="beforeEnter"
                        @enter="enter"
                        @before-leave="beforeLeave"
                        @leave="leave"
                      >
                        <div 
                          v-if="openModules.includes(moduleIndex)"
                          class="overflow-hidden"
                        >
                          <div class="space-y-1 p-2">
                            <div
                              v-for="(lesson, lessonIndex) in module.lessons"
                              :key="lessonIndex"
                              class="group flex items-center justify-between rounded-lg p-3 transition-colors hover:bg-white/5"
                            >
                              <div class="flex items-center space-x-3">
                                <div class="flex h-6 w-6 items-center justify-center">
                                  <CheckCircle 
                                    v-if="lesson.completed"
                                    :size="18" 
                                    class="text-primary"
                                  />
                                  <Circle 
                                    v-else
                                    :size="18" 
                                    class="text-foreground/20"
                                  />
                                </div>
                                <span 
                                  class="text-sm transition-colors"
                                  :class="lesson.completed ? 'text-foreground/60' : 'text-foreground'"
                                >
                                  {{ lesson.title }}
                                </span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </Transition>
                    </div>
                  </div>

                  <!-- Total lessons count -->
                  <div class="mt-4 text-xs text-foreground/40">
                    Total: {{ totalLessons }} lessons
                  </div>
                </div>
              </div>

              <!-- Sidebar -->
              <div class="space-y-5">
                <!-- Tags -->
                <div class="rounded-xl border border-white/5 bg-card p-4">
                  <h3 class="mb-4 text-lg font-semibold text-foreground">Topics</h3>
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="(tag, index) in course.tags"
                      :key="index"
                      class="rounded-full bg-primary/10 px-4 py-2 text-sm font-medium text-primary transition-colors hover:bg-primary/20"
                    >
                      {{ tag }}
                    </span>
                  </div>
                </div>

                <!-- Last Update -->
                <div class="rounded-xl border border-white/5 bg-card p-5">
                  <h3 class="mb-4 text-lg font-semibold text-foreground">Information</h3>
                  <div class="space-y-3">
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-foreground/60">Last update</span>
                      <span class="text-sm font-medium text-foreground">{{ course.lastUpdate }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-foreground/60">Total lessons</span>
                      <span class="text-sm font-medium text-foreground">{{ totalLessons }} lessons</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  Play, 
  BookmarkPlus, 
  Bookmark, 
  CheckCircle, 
  Circle,
  ChevronLeft,
  ChevronDown,
  Tag,
  Award,
  TrendingUp,
  Sparkles
} from 'lucide-vue-next'

import { useUserStore } from '@/stores/userStore'
import { useUiStore } from '@/stores/uiStore'

import NavBar from '@/components/layout/NavBar.vue'
import SideBar from '@/components/layout/SideBar.vue'

// Stores
const userStore = useUserStore()
const uiStore = useUiStore()

// ================================================
// TYPES
// ================================================
interface Lesson {
  title: string
  completed: boolean
}

interface Module {
  title: string
  lessons: Lesson[]
}

interface Course {
  title: string
  description: string
  progressPercentage: number
  tags: string[]
  lastUpdate: string
  modules: Module[]
  difficulty: string
  category: string
}

// ================================================
// PROPS
// ================================================
const props = defineProps<{
  course?: Course
  loading: boolean
}>()

// ================================================
// ACCORDION STATE
// ================================================
const openModules = ref<number[]>([])

const toggleModule = (moduleIndex: number) => {
  if (openModules.value.includes(moduleIndex)) {
    openModules.value = openModules.value.filter(i => i !== moduleIndex)
  } else {
    openModules.value.push(moduleIndex)
  }
}

// ================================================
// ANIMAÇÕES DO ACCORDION
// ================================================
const beforeEnter = (el: Element) => {
  const element = el as HTMLElement
  element.style.height = '0'
  element.style.opacity = '0'
}

const enter = (el: Element) => {
  const element = el as HTMLElement
  element.style.height = element.scrollHeight + 'px'
  element.style.opacity = '1'
  
  setTimeout(() => {
    element.style.height = ''
    element.style.opacity = ''
  }, 300)
}

const beforeLeave = (el: Element) => {
  const element = el as HTMLElement
  element.style.height = element.scrollHeight + 'px'
  element.style.opacity = '1'
}

const leave = (el: Element) => {
  const element = el as HTMLElement
  element.style.height = '0'
  element.style.opacity = '0'
}

// ================================================
// COMPUTED
// ================================================
const totalLessons = computed(() => {
  if (!props.course) return 0
  return props.course.modules.reduce((total, module) => {
    return total + (module.lessons?.length || 0)
  }, 0)
})

// ================================================
// ROUTER
// ================================================
const router = useRouter()

const goBack = () => {
  router.push('/user')
}

// ================================================
// MOBILE DETECTION
// ================================================
const isMobile = ref(window.innerWidth < 1024)

// ================================================
// MENU FUNCTIONS
// ================================================
const handleMenuClick = (menuName: string) => {
  uiStore.setActiveMenu(menuName)
  if (menuName === 'All Series') {
    router.push('/user')
  }
}

// ================================================
// DIFFICULTY ICON HELPER
// ================================================
const getDifficultyIcon = (difficulty: string) => {
  switch(difficulty?.toLowerCase()) {
    case 'beginner': return Award
    case 'intermediate': return TrendingUp
    case 'advanced': return Sparkles
    default: return Award
  }
}

// ================================================
// WATCHLIST TOGGLE STATE
// ================================================
const isInWatchlist = ref(false)
const isHovering = ref(false)
const isAnimating = ref(false)
const showRipple = ref(false)
const isTransitioning = ref(false)
const rippleX = ref(0)
const rippleY = ref(0)
const rippleSize = ref(0)
const watchlistButton = ref<HTMLElement | null>(null)

// Timeouts
let hoverTimeout: ReturnType<typeof setTimeout> | null = null
let leaveTimeout: ReturnType<typeof setTimeout> | null = null

// ================================================
// COMPUTED PROPERTIES (WATCHLIST)
// ================================================

// Dynamic icon
const watchlistIcon = computed(() => {
  return isInWatchlist.value ? Bookmark : BookmarkPlus
})

// Dynamic text
const watchlistButtonText = computed(() => {
  if (isInWatchlist.value) {
    return isHovering.value ? 'Remove' : 'Added ✓'
  }
  return 'Add to Watchlist'
})

// Dynamic classes
const buttonClasses = computed(() => {
  if (isInWatchlist.value) {
    if (isHovering.value) {
      return 'border-red-500/50 bg-primary/10 text-red-400 hover:bg-red-500/10'
    } else {
      return 'border-primary/30 bg-primary/10 text-primary'
    }
  } else {
    return 'border-white/5 bg-card text-foreground/80 hover:bg-white/5 hover:text-foreground'
  }
})

// ================================================
// HOVER FUNCTIONS
// ================================================
const handleMouseEnter = () => {
  if (!isInWatchlist.value) return
  
  if (leaveTimeout) {
    clearTimeout(leaveTimeout)
    leaveTimeout = null
  }
  
  hoverTimeout = setTimeout(() => {
    isHovering.value = true
    hoverTimeout = null
  }, 300)
}

const handleMouseLeave = () => {
  if (!isInWatchlist.value) return
  
  if (hoverTimeout) {
    clearTimeout(hoverTimeout)
    hoverTimeout = null
  }
  
  leaveTimeout = setTimeout(() => {
    isHovering.value = false
    leaveTimeout = null
  }, 200)
}

// ================================================
// TOGGLE WATCHLIST
// ================================================
const toggleWatchlist = (event: MouseEvent) => {
  event.preventDefault()
  
  if (isTransitioning.value) return
  
  isTransitioning.value = true
  
  // Icon animation
  isAnimating.value = true
  setTimeout(() => {
    isAnimating.value = false
  }, 200)
  
  // Ripple effect
  if (watchlistButton.value) {
    const rect = watchlistButton.value.getBoundingClientRect()
    rippleX.value = event.clientX - rect.left
    rippleY.value = event.clientY - rect.top
    rippleSize.value = Math.max(rect.width, rect.height) * 2
    
    showRipple.value = true
    
    setTimeout(() => {
      showRipple.value = false
    }, 600)
  }
  
  // Toggle state
  setTimeout(() => {
    isInWatchlist.value = !isInWatchlist.value
    
    if (isInWatchlist.value === false) {
      isHovering.value = false
    }
    
    setTimeout(() => {
      isTransitioning.value = false
    }, 300)
  }, 150)
}

// ================================================
// WINDOW RESIZE HANDLER
// ================================================
const handleResize = () => {
  isMobile.value = window.innerWidth < 1024
  if (!isMobile.value) uiStore.closeMobileMenu()
}

// ================================================
// LIFECYCLE
// ================================================
onMounted(() => {
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  
  // Clean up timeouts
  if (hoverTimeout) clearTimeout(hoverTimeout)
  if (leaveTimeout) clearTimeout(leaveTimeout)
})
</script>

<style scoped>
/* ================================================
   BASE STYLES
   ================================================ */
button, .group {
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.bg-primary {
  transition: width 0.3s ease-out;
}

.group:hover .text-foreground {
  color: var(--color-primary);
}

*:focus-visible {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
}

/* ================================================
   WATCHLIST BUTTON
   ================================================ */
.watchlist-btn {
  position: relative;
  overflow: hidden;
  transition: all 0.2s ease;
  width: 200px;
  justify-content: flex-start !important;
  padding-left: 16px !important;
  text-align: left;
}

.watchlist-btn:active:not(:disabled) {
  transform: scale(0.98);
}

/* Icon positioning */
.watchlist-btn svg {
  margin-right: 8px !important;
  flex-shrink: 0;
}

/* Text positioning */
.watchlist-btn span {
  flex: 1;
  text-align: left !important;
  white-space: nowrap;
}

/* Ripple effect */
.ripple {
  position: absolute;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.3);
  transform: translate(-50%, -50%) scale(0);
  animation: ripple-animation 0.6s ease-out;
  pointer-events: none;
  z-index: 10;
}

@keyframes ripple-animation {
  0% {
    transform: translate(-50%, -50%) scale(0);
    opacity: 0.5;
  }
  100% {
    transform: translate(-50%, -50%) scale(1);
    opacity: 0;
  }
}

/* Icon animation */
.scale-110 {
  transform: scale(1.1);
}

/* Disabled state */
.watchlist-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* ================================================
   ACCORDION ANIMATIONS
   ================================================ */
.v-enter-active,
.v-leave-active {
  transition: all 0.3s ease-out;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
  height: 0;
}

/* Rotação suave da seta */
.rotate-180 {
  transform: rotate(180deg);
}

/* ================================================
   SKELETON ANIMATIONS
   ================================================ */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>