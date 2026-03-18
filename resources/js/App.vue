<template>
  <RouterView />
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
import { onMounted, onUnmounted } from 'vue'
import { useThemeStore } from '@/stores/themeStore'

// ================================================
// INICIALIZAÇÃO DA STORE DE TEMA
// ================================================
const themeStore = useThemeStore()

// Opcional: Garantir que o tema é aplicado quando a app monta
onMounted(() => {
  // A store já se inicializa automaticamente, mas podemos garantir
  // que o tema está aplicado (a store já faz isto no loadTheme)
  console.log('🎨 Tema inicializado:', themeStore.themeMode)
})

// Opcional: Limpeza se necessário
onUnmounted(() => {
  // A store já faz a limpeza dos listeners automaticamente
})
</script>

<style>
/* ================================================
   TRANSIÇÕES GLOBAIS PARA TEMA
   ================================================ */
* {
  transition: background-color 0.3s ease,
              border-color 0.3s ease,
              color 0.3s ease,
              fill 0.3s ease,
              stroke 0.3s ease,
              box-shadow 0.3s ease;
}

/* Exceções para elementos que não devem ter transição */
.no-transition,
.no-transition * {
  transition: none !important;
}

/* ================================================
   ESTILOS BASE (fallback)
   ================================================ */
html, body {
  min-height: 100vh;
  background-color: var(--color-background);
  color: var(--color-foreground);
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* ================================================
   SCROLLBAR GLOBAL
   ================================================ */
::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}

::-webkit-scrollbar-track {
  background: var(--color-background);
}

::-webkit-scrollbar-thumb {
  background: var(--color-border);
  border-radius: 5px;
  border: 2px solid var(--color-background);
}

::-webkit-scrollbar-thumb:hover {
  background: var(--color-primary);
}

/* Firefox scrollbar */
* {
  scrollbar-width: thin;
  scrollbar-color: var(--color-border) var(--color-background);
}
</style>