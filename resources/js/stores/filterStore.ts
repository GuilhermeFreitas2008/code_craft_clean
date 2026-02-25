import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/axios'

export const useFilterStore = defineStore('filter', () => {
  // Estado dos filtros
  const selectedCategories = ref<string[]>([])
  const selectedDifficulties = ref<string[]>([])

  // Opções disponíveis (vindas da BD)
  const availableCategories = ref<string[]>([])
  const availableDifficulties = ref<string[]>([])
  
  // Loading state
  const isLoading = ref(false)

  // Buscar categorias da API
  const fetchCategories = async () => {
    try {
      const response = await api.get('/categories')
      // Guarda as categorias como vêm da BD (com maiúsculas)
      availableCategories.value = response.data.map((cat: any) => cat.name)
    } catch (error) {
      console.error('Erro ao carregar categorias:', error)
      availableCategories.value = ['Back end', 'Front end', 'DevOps', 'Mobile', 'UI/UX Design', 'Data Science']
    }
  }

  // Buscar dificuldades da API
  const fetchDifficulties = async () => {
    try {
      const response = await api.get('/difficulties')
      // Guarda as dificuldades como vêm da BD (com maiúsculas)
      availableDifficulties.value = response.data.map((diff: any) => diff.name)
    } catch (error) {
      console.error('Erro ao carregar dificuldades:', error)
      availableDifficulties.value = ['Beginner', 'Intermediate', 'Advanced']
    }
  }

  // Carregar tudo
  const fetchFilters = async () => {
    isLoading.value = true
    await Promise.all([fetchCategories(), fetchDifficulties()])
    isLoading.value = false
  }

  // Actions - AGORA CASE INSENSITIVE
  const toggleCategory = (category: string) => {
    // Encontrar a categoria original na lista availableCategories (case insensitive)
    const originalCategory = availableCategories.value.find(
      cat => cat.toLowerCase() === category.toLowerCase()
    ) || category
    
    if (selectedCategories.value.some(c => c.toLowerCase() === originalCategory.toLowerCase())) {
      selectedCategories.value = selectedCategories.value.filter(
        c => c.toLowerCase() !== originalCategory.toLowerCase()
      )
    } else {
      selectedCategories.value.push(originalCategory)
    }
  }

  const toggleDifficulty = (difficulty: string) => {
    // Encontrar a dificuldade original na lista availableDifficulties (case insensitive)
    const originalDifficulty = availableDifficulties.value.find(
      diff => diff.toLowerCase() === difficulty.toLowerCase()
    ) || difficulty
    
    if (selectedDifficulties.value.some(d => d.toLowerCase() === originalDifficulty.toLowerCase())) {
      selectedDifficulties.value = selectedDifficulties.value.filter(
        d => d.toLowerCase() !== originalDifficulty.toLowerCase()
      )
    } else {
      selectedDifficulties.value.push(originalDifficulty)
    }
  }

  const clearFilters = () => {
    selectedCategories.value = []
    selectedDifficulties.value = []
  }

  // Getters (strings para exibir) - CASE INSENSITIVE
  const selectedCategoriesDisplay = (): string => {
    if (selectedCategories.value.length === 0) return ''
    if (selectedCategories.value.length === 1) return selectedCategories.value[0]
    return `${selectedCategories.value.length} categories`
  }

  const selectedDifficultiesDisplay = (): string => {
    if (selectedDifficulties.value.length === 0) return ''
    if (selectedDifficulties.value.length === 1) return selectedDifficulties.value[0]
    return `${selectedDifficulties.value.length} difficulties`
  }

  return {
    selectedCategories,
    selectedDifficulties,
    availableCategories,
    availableDifficulties,
    isLoading,
    fetchFilters,
    toggleCategory,
    toggleDifficulty,
    clearFilters,
    selectedCategoriesDisplay,
    selectedDifficultiesDisplay
  }
}, {
  persist: {
    key: 'codecraft-filters',
    storage: localStorage,
    pick: ['selectedCategories', 'selectedDifficulties']
  }
})