// resources/js/stores/themeStore.ts
import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export type ThemeMode = 'light' | 'dark' | 'system'

export const useThemeStore = defineStore('theme', () => {
  // Estado
  const themeMode = ref<ThemeMode>('dark')
  const effectiveTheme = ref<'light' | 'dark'>('dark')

  // Detetar tema do sistema
  const detectSystemTheme = () => {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    return prefersDark ? 'dark' : 'light'
  }

  // Atualizar tema efetivo
  const updateEffectiveTheme = () => {
    if (themeMode.value === 'system') {
      effectiveTheme.value = detectSystemTheme()
    } else {
      effectiveTheme.value = themeMode.value
    }
  }

  // Aplicar tema ao documento
  const applyTheme = () => {
    const root = document.documentElement
    root.classList.remove('light-theme', 'dark-theme')
    root.classList.add(`${effectiveTheme.value}-theme`)
  }

  // Mudar tema
  const setTheme = (mode: ThemeMode) => {
    themeMode.value = mode
    updateEffectiveTheme()
    applyTheme()
    localStorage.setItem('theme_mode', mode)
  }

  // Carregar tema guardado
  const loadTheme = () => {
    const saved = localStorage.getItem('theme_mode') as ThemeMode | null
    if (saved) {
      themeMode.value = saved
    }
    updateEffectiveTheme()
    applyTheme()
  }

  // Watch para mudanças no sistema (modo system)
  let mediaQuery: MediaQueryList | null = null
  
  if (typeof window !== 'undefined') {
    mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    mediaQuery.addEventListener('change', () => {
      if (themeMode.value === 'system') {
        updateEffectiveTheme()
        applyTheme()
      }
    })
  }

  // Inicializar
  loadTheme()

  return {
    themeMode,
    effectiveTheme,
    setTheme,
  }
})