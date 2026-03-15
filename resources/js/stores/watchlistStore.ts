import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/axios'
import { useUserStore } from './userStore'

export const useWatchlistStore = defineStore('watchlist', () => {
    const items = ref<number[]>([]) // Array com os IDs dos cursos
    const isLoading = ref(false)

    // ================================================
    // Buscar watchlist do utilizador
    // ================================================
    const fetchWatchlist = async () => {
        const userStore = useUserStore()
        if (!userStore.isAuthenticated()) {
            items.value = []
            return
        }

        isLoading.value = true
        try {
            const response = await api.get('/watchlist')
            items.value = response.data.map((course: any) => course.id)
            console.log('📦 Watchlist carregada:', items.value)
        } catch (error) {
            console.error('Erro ao carregar watchlist:', error)
        } finally {
            isLoading.value = false
        }
    }

    // ================================================
    // Adicionar curso à watchlist
    // ================================================
    const addToWatchlist = async (courseId: number) => {
        const userStore = useUserStore()
        if (!userStore.isAuthenticated()) return

        try {
            await api.post(`/watchlist/${courseId}`)
            items.value.push(courseId)
            console.log('✅ Adicionado à watchlist:', courseId)
        } catch (error) {
            console.error('Erro ao adicionar à watchlist:', error)
            throw error
        }
    }

    // ================================================
    // Remover curso da watchlist
    // ================================================
    const removeFromWatchlist = async (courseId: number) => {
        const userStore = useUserStore()
        if (!userStore.isAuthenticated()) return

        try {
            await api.delete(`/watchlist/${courseId}`)
            items.value = items.value.filter(id => id !== courseId)
            console.log('❌ Removido da watchlist:', courseId)
        } catch (error) {
            console.error('Erro ao remover da watchlist:', error)
            throw error
        }
    }

    // ================================================
    // Verificar se um curso está na watchlist
    // ================================================
    const isInWatchlist = (courseId: number): boolean => {
        return items.value.includes(courseId)
    }

    // ================================================
    // Alternar estado (adicionar/remover)
    // ================================================
    const toggleWatchlist = async (courseId: number) => {
        if (isInWatchlist(courseId)) {
            await removeFromWatchlist(courseId)
        } else {
            await addToWatchlist(courseId)
        }
    }

    // ================================================
    // Limpar store (útil para logout)
    // ================================================
    const clearWatchlist = () => {
        items.value = []
    }

    return {
        items,
        isLoading,
        fetchWatchlist,
        addToWatchlist,
        removeFromWatchlist,
        isInWatchlist,
        toggleWatchlist,
        clearWatchlist
    }
}, {
    persist: {
        key: 'codecraft-watchlist',
        storage: localStorage,
        pick: ['items'] // Só persiste os items, não o loading state
    }
})