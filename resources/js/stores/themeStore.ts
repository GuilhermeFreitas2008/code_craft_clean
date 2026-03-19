// resources/js/stores/themeStore.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useUserStore } from './userStore'

export type ThemeMode = 'light' | 'dark' | 'system'

export const useThemeStore = defineStore('theme', () => {
  // Estado
  const themeMode = ref<ThemeMode>('dark')
  const effectiveTheme = ref<'light' | 'dark'>('dark')
  const userStore = useUserStore()

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

  // Mudar tema (APENAS visual e localStorage, NÃO guarda na BD)
  const setTheme = (mode: ThemeMode) => {
    themeMode.value = mode
    updateEffectiveTheme()
    applyTheme()
    localStorage.setItem('theme_mode', mode)
  }

  // Carregar tema guardado (PRIORIDADE: BD > localStorage)
  const loadTheme = async () => {
    // 1. Se user estiver autenticado, buscar da BD primeiro
    if (userStore.isAuthenticated()) {
      try {
        const prefs = await userStore.fetchPreferences()
        if (prefs?.theme) {
          themeMode.value = prefs.theme as ThemeMode
          updateEffectiveTheme()
          applyTheme()
          localStorage.setItem('theme_mode', prefs.theme) // atualiza localStorage
          console.log('🎨 Tema carregado da BD:', prefs.theme)
          return
        }
      } catch (error) {
        console.error('Erro ao carregar tema da BD:', error)
      }
    }
    
    // 2. Fallback para localStorage
    const saved = localStorage.getItem('theme_mode') as ThemeMode | null
    if (saved) {
      themeMode.value = saved
      updateEffectiveTheme()
      applyTheme()
      console.log('🎨 Tema carregado do localStorage:', saved)
    }
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
    loadTheme, // expor para uso externo se necessário
  }
})