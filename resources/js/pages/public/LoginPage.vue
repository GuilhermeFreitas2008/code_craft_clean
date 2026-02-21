<template>
  <div class="flex min-h-screen flex-col lg:flex-row">
    <!-- Left Side - Branding/Visual - Hidden on medium screens and below -->
    <div 
      v-show="showLeftPanel"
      class="relative hidden flex-1 items-center justify-center overflow-hidden bg-card p-8 lg:flex lg:p-12"
      :class="['bg-grid-white-large']"
    >
      <!-- Aura no centro -->
      <div class="absolute inset-0 flex items-center justify-center">
        <div class="absolute h-[600px] w-[600px] rounded-full bg-primary/5 blur-3xl"></div>
        <div class="absolute h-[400px] w-[400px] rounded-full bg-primary/10 blur-2xl"></div>
        <div class="absolute h-2 w-2 rounded-full bg-primary/30 blur-sm" style="top: 45%; left: 48%;"></div>
        <div class="absolute h-3 w-3 rounded-full bg-primary/20 blur-sm" style="top: 52%; left: 55%;"></div>
        <div class="absolute h-2 w-2 rounded-full bg-primary/30 blur-sm" style="top: 48%; left: 45%;"></div>
        <div class="absolute h-4 w-4 rounded-full bg-primary/10 blur-md" style="top: 55%; left: 50%;"></div>
        <div class="absolute h-2 w-2 rounded-full bg-primary/30 blur-sm" style="top: 42%; left: 52%;"></div>
        <div class="absolute h-3 w-3 rounded-full bg-primary/20 blur-sm" style="top: 58%; left: 44%;"></div>
        <div class="absolute h-2 w-2 rounded-full bg-primary/30 blur-sm" style="top: 47%; left: 58%;"></div>
      </div>

      <!-- Background pattern with icons -->
      <div class="absolute inset-0">
        <div
          v-for="(item, index) in backgroundIcons"
          :key="index"
          class="absolute text-white/5 transition-transform hover:text-white/10"
          :style="{
            top: item.top,
            left: item.left,
            transform: `rotate(${item.rotation}deg)`,
            animation: `float ${item.duration || 25}s infinite ${item.delay}s ease-in-out`
          }"
        >
          <component :is="item.icon" :size="item.size" />
        </div>
      </div>

      <!-- Centered content with animation -->
      <div class="relative z-20 max-w-md text-center animate-fade-in-up">
        <div class="mb-0 flex justify-center">
          <img 
            src="/images/Logo.svg"  
            alt="CodeCraft Logo" 
            class="h-40 w-50"
          />
        </div>
        
        <h1 class="mb-4 text-5xl font-bold tracking-tight text-foreground lg:text-6xl">
          Code<span class="text-primary">Craft</span>
        </h1>
        
        <p class="text-xl text-foreground/60">
          Your development platform starts here.
        </p>
      </div>
    </div>

    <!-- Right Side - Form -->
    <div 
      class="flex flex-1 items-center justify-center bg-background p-8 lg:p-12"
      :class="{
        'w-full': !showLeftPanel,
        'lg:w-auto': showLeftPanel
      }"
    >
      <div class="w-full max-w-md space-y-8">
        <!-- Form header -->
        <div class="space-y-2 text-center animate-fade-in-up">
          <h2 class="text-3xl font-bold tracking-tight text-foreground">
            Welcome!
          </h2>
          <p class="text-base text-foreground/60">
            Sign in to your account to continue
          </p>
        </div>

        <!-- Error Alert -->
        <div
          v-if="authError"
          class="rounded-lg border border-red-500/20 bg-red-500/10 px-4 py-3 animate-fade-in-up"
        >
          <p class="text-sm text-red-400">
            {{ authError }}
          </p>
        </div>

        <!-- Form -->
        <form @submit="handleSubmit" class="space-y-6">
          <!-- Email Field -->
          <div class="space-y-2">
            <label 
              for="email" 
              class="block text-sm font-medium text-foreground/80"
            >
              Email
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              class="w-full rounded-lg border px-4 py-3 text-foreground placeholder-foreground/40 transition-all duration-200 hover:border-primary/50 focus:outline-none focus:ring-2 bg-card/50"
              :class="[
                emailError 
                  ? 'border-red-500/50 focus:border-red-500 focus:ring-red-500/20' 
                  : 'border-border focus:border-primary focus:ring-primary/20'
              ]"
              placeholder="you@example.com"
              :disabled="isLoading"
              @blur="touched.email = true"
              @input="clearAuthError"
            />
            <p v-if="emailError" class="text-xs text-red-400 animate-fade-in-up">
              {{ emailError }}
            </p>
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label 
                for="password" 
                class="block text-sm font-medium text-foreground/80"
              >
                Password
              </label>
              <a
                href="#"
                class="text-xs text-foreground/60 transition-colors hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2 focus:ring-offset-background"
              >
                Forgot password?
              </a>
            </div>
            
            <div class="relative">
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                class="w-full rounded-lg border px-4 py-3 text-foreground placeholder-foreground/40 transition-all duration-200 hover:border-primary/50 focus:outline-none focus:ring-2 bg-card/50"
                :class="[
                  passwordError 
                    ? 'border-red-500/50 focus:border-red-500 focus:ring-red-500/20' 
                    : 'border-border focus:border-primary focus:ring-primary/20'
                ]"
                placeholder="••••••••"
                :disabled="isLoading"
                @blur="touched.password = true"
                @input="clearAuthError"
              />
              <button
                type="button"
                @click="togglePasswordVisibility"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-foreground/60 transition-colors hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 rounded-md p-1"
                :disabled="isLoading"
              >
                <component :is="showPassword ? EyeOff : Eye" :size="20" />
              </button>
            </div>
            <p v-if="passwordError" class="text-xs text-red-400 animate-fade-in-up">
              {{ passwordError }}
            </p>
          </div>

          <!-- Remember me -->
          <div class="flex items-center">
            <label class="flex cursor-pointer items-center space-x-2">
              <input
                v-model="rememberMe"
                type="checkbox"
                class="h-4 w-4 rounded border-border bg-card/50 text-primary transition-colors focus:ring-2 focus:ring-primary/20 focus:ring-offset-0 focus:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="isLoading"
              />
              <span class="text-sm text-foreground/60">Remember me</span>
            </label>
          </div>

          <!-- Sign in button -->
          <button
            type="submit"
            :disabled="isLoading || !isFormValid"
            class="relative w-full rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-2 focus:ring-offset-background disabled:cursor-not-allowed disabled:opacity-70 overflow-hidden group"
          >
            <span :class="{ 'opacity-0': isLoading }">
              Sign in
            </span>
            
            <!-- Loading spinner -->
            <div
              v-if="isLoading"
              class="absolute inset-0 flex items-center justify-center"
            >
              <svg 
                class="h-5 w-5 animate-spin text-white" 
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24"
              >
                <circle 
                  class="opacity-25" 
                  cx="12" 
                  cy="12" 
                  r="10" 
                  stroke="currentColor" 
                  stroke-width="4"
                />
                <path 
                  class="opacity-75" 
                  fill="currentColor" 
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
              </svg>
            </div>
          </button>
        </form>

        <!-- Footer -->
        <div class="text-center text-sm text-foreground/60">
          <span>Don't have an account? </span>
          <router-link
            to="/register"
            class="font-medium text-primary transition-colors hover:text-primary/80 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2 focus:ring-offset-background"
          >
            Sign up
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/axios'
import { 
  Code2, Globe, Database, Server, Hash, Eye,  EyeOff,
  Terminal, Cpu, Binary, GitBranch, Braces, Cloud, Box,
  Wrench, Workflow, Shield, Zap, Layers, Sparkles
} from 'lucide-vue-next'

const router = useRouter()

// Form state
const showPassword = ref(false)
const email = ref('')
const password = ref('')
const rememberMe = ref(false)

// Loading state
const isLoading = ref(false)

// Validation state
const touched = ref({
  email: false,
  password: false
})

// Auth error state
const authError = ref('')

// State to control left panel visibility
const showLeftPanel = ref(true)
const BREAKPOINT = 1024

// Email validation
const emailError = computed(() => {
  if (!touched.value.email) return ''
  if (!email.value) return 'Email is required'
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) return 'Please enter a valid email address'
  return ''
})

// Password validation
const passwordError = computed(() => {
  if (!touched.value.password) return ''
  if (!password.value) return 'Password is required'
  return ''
})

// Form validation
const isFormValid = computed(() => {
  return !emailError.value && !passwordError.value && email.value && password.value
})

// Clear auth error when user starts typing
const clearAuthError = () => {
  authError.value = ''
}

// Toggle password visibility
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

// Form submit handler - VERSÃO LIMPA
const handleSubmit = async (e: Event) => {
  e.preventDefault()
  
  // Mark all fields as touched
  touched.value = {
    email: true,
    password: true
  }
  
  if (!isFormValid.value) return

  isLoading.value = true
  authError.value = ''

  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value
    })

    const { user, token } = response.data

    // Verificar se os dados existem
    if (!user || !token) {
      throw new Error('Resposta inválida do servidor')
    }

    // Guardar dados
    localStorage.setItem('auth_token', token)
    localStorage.setItem('user', JSON.stringify(user))

    if (rememberMe.value) {
      localStorage.setItem('rememberMe', 'true')
    }

    // REDIRECIONAMENTO - user normal (role_id = 2)
    if (user.role_id === 2) {
      window.location.href = '/user'
    }
    // Admin (role_id = 1)
    else if (user.role_id === 1) {
      window.location.href = '/admin'
    }
    else {
      authError.value = 'Acesso não autorizado para este tipo de usuário'
    }

  } catch (error: any) {
    console.error('Erro no login:', error)
    
    if (error.response) {
      authError.value = error.response.data?.error || 'Erro no servidor'
    } else if (error.request) {
      authError.value = 'Servidor não respondeu'
    } else {
      authError.value = error.message || 'Email ou password inválidos'
    }
  } finally {
    isLoading.value = false
  }
}

// Check window size for responsive layout
const checkWindowSize = () => {
  showLeftPanel.value = window.innerWidth >= BREAKPOINT
}

// Lifecycle hooks
onMounted(() => {
  checkWindowSize()
  window.addEventListener('resize', checkWindowSize)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkWindowSize)
})

// Background icons array
const backgroundIcons = [
  { icon: Code2, rotation: -12, delay: 0, top: '2%', left: '3%', size: 24, duration: 28 },
  { icon: Database, rotation: 8, delay: 0.5, top: '7%', left: '12%', size: 26, duration: 32 },
  { icon: GitBranch, rotation: -5, delay: 1, top: '1%', left: '22%', size: 22, duration: 30 },
  { icon: Braces, rotation: 15, delay: 1.5, top: '8%', left: '31%', size: 24, duration: 35 },
  { icon: Globe, rotation: -8, delay: 2, top: '3%', left: '45%', size: 26, duration: 29 },
  { icon: Server, rotation: 10, delay: 2.5, top: '6%', left: '55%', size: 24, duration: 31 },
  { icon: Cloud, rotation: -15, delay: 3, top: '2%', left: '65%', size: 28, duration: 33 },
  { icon: Zap, rotation: 6, delay: 3.5, top: '9%', left: '75%', size: 22, duration: 27 },
  { icon: Cpu, rotation: -10, delay: 4, top: '4%', left: '88%', size: 26, duration: 34 },
  { icon: Binary, rotation: 12, delay: 4.5, top: '1%', left: '95%', size: 24, duration: 30 },
  { icon: Workflow, rotation: -7, delay: 5, top: '7%', left: '82%', size: 28, duration: 32 },
  { icon: Box, rotation: 14, delay: 5.5, top: '18%', left: '1%', size: 26, duration: 29 },
  { icon: Wrench, rotation: -9, delay: 6, top: '25%', left: '8%', size: 24, duration: 33 },
  { icon: Shield, rotation: 11, delay: 6.5, top: '14%', left: '15%', size: 28, duration: 31 },
  { icon: Layers, rotation: -13, delay: 7, top: '22%', left: '24%', size: 22, duration: 35 },
  { icon: Terminal, rotation: 7, delay: 7.5, top: '35%', left: '5%', size: 26, duration: 30 },
  { icon: Hash, rotation: -6, delay: 8, top: '42%', left: '12%', size: 24, duration: 28 },
  { icon: Database, rotation: 9, delay: 8.5, top: '38%', left: '20%', size: 28, duration: 34 },
  { icon: Sparkles, rotation: -12, delay: 9, top: '30%', left: '45%', size: 26, duration: 32 },
  { icon: Code2, rotation: 8, delay: 9.5, top: '45%', left: '50%', size: 24, duration: 29 },
  { icon: Zap, rotation: -5, delay: 10, top: '35%', left: '60%', size: 22, duration: 33 },
  { icon: Server, rotation: 13, delay: 10.5, top: '28%', left: '78%', size: 26, duration: 31 },
  { icon: Globe, rotation: -11, delay: 11, top: '40%', left: '85%', size: 24, duration: 35 },
  { icon: Cpu, rotation: 6, delay: 11.5, top: '33%', left: '92%', size: 28, duration: 30 },
  { icon: Binary, rotation: -9, delay: 12, top: '48%', left: '70%', size: 26, duration: 28 },
  { icon: GitBranch, rotation: 15, delay: 12.5, top: '55%', left: '82%', size: 24, duration: 34 },
  { icon: Braces, rotation: -7, delay: 13, top: '62%', left: '90%', size: 26, duration: 32 },
  { icon: Cloud, rotation: 10, delay: 13.5, top: '58%', left: '75%', size: 22, duration: 29 },
  { icon: Workflow, rotation: -14, delay: 14, top: '65%', left: '95%', size: 28, duration: 33 },
  { icon: Box, rotation: 8, delay: 14.5, top: '72%', left: '2%', size: 24, duration: 31 },
  { icon: Wrench, rotation: -6, delay: 15, top: '80%', left: '10%', size: 26, duration: 30 },
  { icon: Shield, rotation: 12, delay: 15.5, top: '75%', left: '18%', size: 22, duration: 35 },
  { icon: Layers, rotation: -10, delay: 16, top: '85%', left: '6%', size: 28, duration: 28 },
  { icon: Terminal, rotation: 9, delay: 16.5, top: '78%', left: '35%', size: 24, duration: 32 },
  { icon: Hash, rotation: -13, delay: 17, top: '82%', left: '45%', size: 26, duration: 34 },
  { icon: Database, rotation: 5, delay: 17.5, top: '70%', left: '55%', size: 22, duration: 29 },
  { icon: Server, rotation: -8, delay: 18, top: '88%', left: '50%', size: 28, duration: 33 },
  { icon: Cpu, rotation: 11, delay: 18.5, top: '73%', left: '68%', size: 24, duration: 30 },
  { icon: Binary, rotation: -15, delay: 19, top: '79%', left: '80%', size: 26, duration: 32 },
  { icon: GitBranch, rotation: 7, delay: 19.5, top: '84%', left: '72%', size: 22, duration: 35 },
  { icon: Braces, rotation: -9, delay: 20, top: '90%', left: '88%', size: 28, duration: 29 },
  { icon: Cloud, rotation: 13, delay: 20.5, top: '77%', left: '93%', size: 24, duration: 31 },
  { icon: Sparkles, rotation: -12, delay: 21, top: '95%', left: '1%', size: 26, duration: 34 },
  { icon: Code2, rotation: 8, delay: 21.5, top: '98%', left: '48%', size: 22, duration: 28 },
  { icon: Zap, rotation: -5, delay: 22, top: '92%', left: '98%', size: 24, duration: 33 },
]
</script>

<style scoped>
/* Smooth animations for background icons */
@keyframes float {
  0%, 100% {
    transform: translateY(0) rotate(var(--rotation, 0deg));
  }
  50% {
    transform: translateY(-12px) rotate(var(--rotation, 0deg));
  }
}

/* Autofill customization */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
input:-webkit-autofill:active {
  -webkit-background-clip: text;
  -webkit-text-fill-color: #f8fafc;
  transition: background-color 5000s ease-in-out 0s;
  box-shadow: inset 0 0 0px 1000px transparent;
}

/* Remove default browser validation styles */
input:invalid,
input:required {
  box-shadow: none;
}
</style>