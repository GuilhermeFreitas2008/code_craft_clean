<!-- components/lessons/LessonSidebar.vue -->
<template>
  <!-- Desktop Sidebar -->
  <aside 
    v-if="!isMobile"
    class="fixed left-0 top-16 z-30 h-[calc(100vh-4rem)] w-80 flex-col border-r border-border bg-card p-6 lg:flex transition-all duration-300 ease-out"
    :class="open ? 'translate-x-0 opacity-100' : '-translate-x-full opacity-0'"
  >
    <!-- Back to Course Button -->
    <button 
      @click="goBack" 
      class="flex items-center text-foreground/60 transition-colors hover:text-primary group -ml-2 mb-6"
    >
      <ChevronLeft :size="20" class="transition-transform group-hover:-translate-x-1" />
      <span class="text-base font-medium ml-1.5">Back to course</span>
    </button>

    <!-- Progresso Global do Curso -->
    <div class="mb-8">
      <div class="flex items-center justify-between mb-2">
        <span class="text-base font-medium text-foreground/80">Your progress</span>
        <span class="text-base font-semibold text-primary">{{ courseProgress }}%</span>
      </div>
      
      <!-- Barra de progresso -->
      <div class="h-2 w-full rounded-full bg-white/10">
        <div 
          class="h-full rounded-full bg-primary transition-all duration-300"
          :style="{ width: `${courseProgress}%` }"
        ></div>
      </div>
      
      <!-- Texto de conclusão -->
      <p class="mt-2 text-xs font-medium text-foreground/40">
        {{ completedLessons }}/{{ totalLessons }} lessons
      </p>
    </div>

    <div class="mb-4">
      <h2 class="text-sm font-semibold uppercase tracking-wider text-foreground/40">Course Content</h2>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar">
      <div class="space-y-3" v-if="modules.length > 0">
        <div
          v-for="module in modules"
          :key="module.id"
          class="rounded-lg border border-white/5 overflow-hidden"
        >
          <!-- Module Header -->
          <button
            @click="toggleModule(module.id)"
            class="flex w-full items-center justify-between bg-card px-4 py-2.5 text-left hover:bg-white/5 transition-colors"
          >
            <div class="flex items-center space-x-2">
              <span class="text-sm font-medium text-foreground">{{ module.title }}</span>
              <span class="text-xs font-medium text-foreground/40">{{ module.lessons.length }}</span>
            </div>
            <ChevronDown 
              :size="16" 
              class="text-foreground/40 transition-transform duration-200"
              :class="{ 'rotate-180': openModules.includes(module.id) }"
            />
          </button>

          <!-- Module Lessons -->
          <Transition
            @before-enter="beforeEnter"
            @enter="enter"
            @before-leave="beforeLeave"
            @leave="leave"
          >
            <div v-if="openModules.includes(module.id)" class="overflow-hidden">
              <div class="space-y-0.5 p-1">
                <div
                  v-for="lesson in module.lessons"
                  :key="lesson.id"
                  @click="selectLesson(lesson.id)"
                  class="flex items-center space-x-3 rounded-md px-3 py-2 cursor-pointer transition-colors"
                  :class="[
                    lesson.id === currentLessonId
                      ? 'bg-primary/10 text-primary'
                      : 'hover:bg-white/5 text-foreground/70 hover:text-foreground'
                  ]"
                >
                  <CheckCircle 
                    v-if="lesson.completed"
                    :size="14" 
                    class="text-primary"
                  />
                  <Circle 
                    v-else
                    :size="14" 
                    class="text-foreground/20"
                  />
                  
                  <span class="text-sm font-medium">{{ lesson.title }}</span>
                </div>
              </div>
            </div>
          </Transition>
        </div>
      </div>
      <div v-else class="text-center text-foreground/40 py-4">
        No modules available
      </div>
    </div>
  </aside>

  <!-- Mobile Sidebar -->
  <Teleport to="body">
    <!-- Overlay escuro com blur -->
    <div 
      v-if="isMobile && open"
      class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden transition-opacity duration-300"
      :class="open ? 'opacity-100' : 'opacity-0'"
      @click="closeSidebar"
    ></div>
    
    <!-- Sidebar mobile -->
    <div 
      v-if="isMobile"
      class="fixed left-0 top-0 z-50 h-screen w-80 bg-card p-6 transition-all duration-300 ease-out lg:hidden shadow-xl overflow-y-auto custom-scrollbar"
      :class="open ? 'translate-x-0 opacity-100' : '-translate-x-full opacity-0'"
    >
      <!-- Logo no mobile -->
      <div class="mb-4 flex items-center space-x-2 pb-4 border-b border-white/5">
        <img src="/images/Logo.svg" alt="CodeCraft" class="h-8 w-8" />
        <span class="text-lg font-bold text-foreground">Code<span class="text-primary">Craft</span></span>
      </div>

      <!-- Back to Course Button (Mobile) -->
      <button 
        @click="goBackMobile" 
        class="flex items-center text-foreground/60 transition-colors hover:text-primary group -ml-2 mb-6"
      >
        <ChevronLeft :size="20" class="transition-transform group-hover:-translate-x-1" />
        <span class="text-base font-medium ml-1.5">Back to course</span>
      </button>

      <!-- Progresso Global - Mobile -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
          <span class="text-base font-medium text-foreground/80">Progress</span>
          <span class="text-base font-semibold text-primary">{{ courseProgress }}%</span>
        </div>
        
        <div class="h-2 w-full rounded-full bg-white/10">
          <div 
            class="h-full rounded-full bg-primary transition-all duration-300"
            :style="{ width: `${courseProgress}%` }"
          ></div>
        </div>
        
        <p class="mt-2 text-xs font-medium text-foreground/40">
          {{ completedLessons }}/{{ totalLessons }} lessons
        </p>
      </div>

      <div class="mb-4">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-foreground/40">Course Content</h2>
      </div>

      <div class="space-y-3 pb-6">
        <div
          v-for="module in modules"
          :key="module.id"
          class="rounded-lg border border-white/5 overflow-hidden"
        >
          <!-- Module Header (Mobile) -->
          <button
            @click="toggleModule(module.id)"
            class="flex w-full items-center justify-between bg-card px-4 py-2.5 text-left hover:bg-white/5 transition-colors"
          >
            <div class="flex items-center space-x-2">
              <span class="text-sm font-medium text-foreground">{{ module.title }}</span>
              <span class="text-xs font-medium text-foreground/40">{{ module.lessons.length }}</span>
            </div>
            <ChevronDown 
              :size="16" 
              class="text-foreground/40 transition-transform duration-200"
              :class="{ 'rotate-180': openModules.includes(module.id) }"
            />
          </button>

          <!-- Module Lessons (Mobile) -->
          <Transition
            @before-enter="beforeEnter"
            @enter="enter"
            @before-leave="beforeLeave"
            @leave="leave"
          >
            <div v-if="openModules.includes(module.id)" class="overflow-hidden">
              <div class="space-y-0.5 p-1">
                <div
                  v-for="lesson in module.lessons"
                  :key="lesson.id"
                  @click="selectLessonMobile(lesson.id)"
                  class="flex items-center space-x-3 rounded-md px-3 py-2 cursor-pointer transition-colors"
                  :class="[
                    lesson.id === currentLessonId
                      ? 'bg-primary/10 text-primary'
                      : 'hover:bg-white/5 text-foreground/70 hover:text-foreground'
                  ]"
                >
                  <CheckCircle 
                    v-if="lesson.completed"
                    :size="14" 
                    class="text-primary"
                  />
                  <Circle 
                    v-else
                    :size="14" 
                    class="text-foreground/20"
                  />
                  
                  <span class="text-sm font-medium">{{ lesson.title }}</span>
                </div>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ChevronDown, CheckCircle, Circle, ChevronLeft } from 'lucide-vue-next'
import type { Module } from '@/types/lesson.types'

const router = useRouter()

const props = defineProps<{
  modules: Module[]
  currentLessonId: number
  open: boolean
  isMobile: boolean
  courseId: number
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'lesson-select', lessonId: number): void
}>()

// ================================================
// BACK TO COURSE
// ================================================
const goBack = () => {
  if (props.courseId) {
    router.push(`/course/${props.courseId}`)
  } else {
    router.push('/user')
  }
}

const goBackMobile = () => {
  goBack()
  closeSidebar()
}

// ================================================
// CÁLCULOS DE PROGRESSO
// ================================================
const totalLessons = computed(() => {
  return props.modules.reduce((total, module) => total + module.lessons.length, 0)
})

const completedLessons = computed(() => {
  return props.modules.reduce(
    (total, module) => total + module.lessons.filter(lesson => lesson.completed).length, 
    0
  )
})

const courseProgress = computed(() => {
  if (totalLessons.value === 0) return 0
  return Math.round((completedLessons.value / totalLessons.value) * 100)
})

// ================================================
// ACCORDION STATE
// ================================================
const openModules = ref<number[]>([])

// Auto-expand module that contains current lesson
watch(() => props.currentLessonId, (newLessonId) => {
  if (newLessonId) {
    const moduleWithCurrentLesson = props.modules.find(module => 
      module.lessons.some(lesson => lesson.id === newLessonId)
    )
    if (moduleWithCurrentLesson && !openModules.value.includes(moduleWithCurrentLesson.id)) {
      openModules.value.push(moduleWithCurrentLesson.id)
    }
  }
}, { immediate: true })

const toggleModule = (moduleId: number) => {
  if (openModules.value.includes(moduleId)) {
    openModules.value = openModules.value.filter(id => id !== moduleId)
  } else {
    openModules.value.push(moduleId)
  }
}

// ================================================
// LESSON SELECTION
// ================================================
const selectLesson = (lessonId: number) => {
  emit('lesson-select', lessonId)
}

const selectLessonMobile = (lessonId: number) => {
  emit('lesson-select', lessonId)
  closeSidebar()
}

const closeSidebar = () => {
  emit('close')
}

// ================================================
// ACCORDION ANIMATIONS
// ================================================
const beforeEnter = (el: Element) => {
  (el as HTMLElement).style.height = '0'
}

const enter = (el: Element) => {
  const element = el as HTMLElement
  element.style.height = element.scrollHeight + 'px'
  
  setTimeout(() => {
    element.style.height = ''
  }, 200)
}

const beforeLeave = (el: Element) => {
  const element = el as HTMLElement
  element.style.height = element.scrollHeight + 'px'
}

const leave = (el: Element) => {
  (el as HTMLElement).style.height = '0'
}
</script>

<style scoped>
.v-enter-active,
.v-leave-active {
  transition: height 0.2s ease-out;
}

.v-enter-from,
.v-leave-to {
  height: 0;
}
</style>