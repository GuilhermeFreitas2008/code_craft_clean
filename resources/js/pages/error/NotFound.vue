<template>
  <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-background bg-grid-white-large px-6 text-center">
    
    <div class="pointer-events-none absolute inset-0 bg-background [mask-image:radial-gradient(ellipse_at_center,transparent_20%,black)]"></div>

    <div class="relative z-10 flex flex-col items-center animate-fade-in-up">
      
      <div class="relative mb-2 h-48 w-48 animate-bounce-slow">
        <img 
          src="/images/ErrorFace.svg" 
          alt="Error Face" 
          class="h-full w-full object-contain drop-shadow-[0_0_15px_rgba(59,130,246,0.5)]"
        />
        <div class="absolute -top-2 -right-4 rotate-12 rounded-md bg-red-500 px-2 py-1 text-[10px] font-black text-white animate-pulse">
          RUNTIME_ERROR
        </div>
      </div>

      <div class="space-y-4">
        <div class="flex items-center justify-center space-x-2 text-primary">
          <Terminal :size="18" />
          <span class="text-sm font-mono font-bold tracking-widest uppercase">Status 404</span>
        </div>
        
        <h1 class="text-4xl font-black tracking-tighter text-foreground sm:text-6xl italic uppercase">
            Lost in the <span class="text-primary">Void</span>
        </h1>
        
        <p class="mx-auto max-w-md text-base leading-relaxed text-foreground/60 sm:text-lg">
          The component you're looking for was <span class="text-foreground font-semibold">garbage collected</span> or was never committed to our server.
        </p>
      </div>

      <div class="mt-12 flex flex-col items-center justify-center space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4">
        <button 
          @click="goBack"
          class="group flex items-center space-x-3 rounded-xl border border-border bg-card px-8 py-4 text-sm font-bold text-foreground transition-all hover:border-primary/50 hover:bg-primary/5"
        >
          <ArrowLeft :size="18" class="transition-transform group-hover:-translate-x-1" />
          <span>Step Back</span>
        </button>

        <router-link 
          :to="{ name: 'landing' }"
          class="group flex items-center space-x-3 rounded-xl bg-primary px-8 py-4 text-sm font-bold text-white transition-all hover:bg-primary-hover shadow-lg shadow-primary/20"
        >
          <Home :size="18" />
          <span>Return Home</span>
        </router-link>
      </div>
    </div>

    <div class="absolute bottom-8 z-10 font-mono text-[10px] text-foreground/20 uppercase tracking-[0.2em]">
        Code Craft System v1.0.0 // Connection: Terminated
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import { ArrowLeft, Home, Terminal } from 'lucide-vue-next';

const router = useRouter();

const goBack = () => {
  if (window.history.length > 1) {
    router.back();
  } else {
    router.push({ name: 'landing' });
  }
};
</script>

<style scoped>
h1 {
  text-shadow: 0 0 30px rgba(59, 130, 246, 0.2);
}

.bg-grid-white-large {
    background-color: var(--color-background);
}
</style>