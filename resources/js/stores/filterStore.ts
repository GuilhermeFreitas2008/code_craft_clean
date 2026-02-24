import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useFilterStore = defineStore('filter', () => {
  // Estado dos filtros
  const selectedCategories = ref<string[]>([])
  const selectedDifficulties = ref<string[]>([])

  // Opções disponíveis
  const availableCategories = [
    'Back end', 'Front end', 'DevOps', 'Mobile', 'UI/UX Design', 'Data Science'
  ]
  const availableDifficulties = ['beginner', 'intermediate', 'advanced']

  // Actions
  const toggleCategory = (category: string) => {
    if (selectedCategories.value.includes(category)) {
      selectedCategories.value = selectedCategories.value.filter(c => c !== category)
    } else {
      selectedCategories.value.push(category)
    }
  }

  const toggleDifficulty = (difficulty: string) => {
    if (selectedDifficulties.value.includes(difficulty)) {
      selectedDifficulties.value = selectedDifficulties.value.filter(d => d !== difficulty)
    } else {
      selectedDifficulties.value.push(difficulty)
    }
  }

  const clearFilters = () => {
    selectedCategories.value = []
    selectedDifficulties.value = []
  }

  // Getters (strings para exibir)
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