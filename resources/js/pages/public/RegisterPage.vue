<!-- RegisterPage.vue -->
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
        <!-- Glow principal -->
        <div class="absolute h-[600px] w-[600px] rounded-full bg-primary/5 blur-3xl"></div>
        <!-- Glow secundário para mais profundidade -->
        <div class="absolute h-[400px] w-[400px] rounded-full bg-primary/10 blur-2xl"></div>
        <!-- Partículas brilhantes -->
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
      class="flex flex-1 items-center justify-center bg-background p-6 lg:p-10"
      :class="{
        'w-full': !showLeftPanel,
        'lg:w-auto': showLeftPanel
      }"
    >
      <div class="w-full max-w-md space-y-5">
        <!-- Form header -->
        <div class="space-y-1 text-center animate-fade-in-up">
          <h2 class="text-2xl font-bold tracking-tight text-foreground lg:text-3xl">
            Create an account
          </h2>
          <p class="text-sm text-foreground/60">
            Sign up to start your development journey
          </p>
        </div>

        <!-- Form -->
        <form @submit="handleSubmit" class="space-y-4">
          <!-- Username Field -->
          <div class="space-y-1">
            <label 
              for="username" 
              class="block text-xs font-medium text-foreground/80"
            >
              Username
            </label>
            <input
              id="username"
              v-model="username"
              type="text"
              class="w-full rounded-lg border px-3 py-2.5 text-sm text-foreground placeholder-foreground/40 transition-all duration-200 hover:border-primary/50 focus:outline-none focus:ring-2 bg-card/50"
              :class="[
                errors.username 
                  ? 'border-red-500/50 focus:border-red-500 focus:ring-red-500/20' 
                  : 'border-border focus:border-primary focus:ring-primary/20'
              ]"
              placeholder="johndoe"
              :disabled="isLoading"
              @blur="touched.username = true"
            />
            <p v-if="errors.username" class="text-xs text-red-400 animate-fade-in-up">
              {{ errors.username }}
            </p>
          </div>

          <!-- Email Field -->
          <div class="space-y-1">
            <label 
              for="email" 
              class="block text-xs font-medium text-foreground/80"
            >
              Email
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              class="w-full rounded-lg border px-3 py-2.5 text-sm text-foreground placeholder-foreground/40 transition-all duration-200 hover:border-primary/50 focus:outline-none focus:ring-2 bg-card/50"
              :class="[
                errors.email 
                  ? 'border-red-500/50 focus:border-red-500 focus:ring-red-500/20' 
                  : 'border-border focus:border-primary focus:ring-primary/20'
              ]"
              placeholder="you@example.com"
              :disabled="isLoading"
              @blur="touched.email = true"
            />
            <p v-if="errors.email" class="text-xs text-red-400 animate-fade-in-up">
              {{ errors.email }}
            </p>
          </div>

          <!-- Password Field -->
          <div class="space-y-1">
            <label 
              for="password" 
              class="block text-xs font-medium text-foreground/80"
            >
              Password
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                class="w-full rounded-lg border px-3 py-2.5 text-sm text-foreground placeholder-foreground/40 transition-all duration-200 hover:border-primary/50 focus:outline-none focus:ring-2 bg-card/50"
                :class="[
                  errors.password 
                    ? 'border-red-500/50 focus:border-red-500 focus:ring-red-500/20' 
                    : 'border-border focus:border-primary focus:ring-primary/20'
                ]"
                placeholder="••••••••"
                :disabled="isLoading"
                @blur="touched.password = true"
              />
              <button
                type="button"
                @click="togglePasswordVisibility"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-foreground/60 transition-colors hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 rounded-md p-1"
                :disabled="isLoading"
              >
                <component :is="showPassword ? EyeOff : Eye" :size="18" />
              </button>
            </div>
            <p v-if="errors.password" class="text-xs text-red-400 animate-fade-in-up">
              {{ errors.password }}
            </p>
          </div>

          <!-- Confirm Password Field -->
          <div class="space-y-1">
            <label 
              for="confirmPassword" 
              class="block text-xs font-medium text-foreground/80"
            >
              Confirm Password
            </label>
            <div class="relative">
              <input
                id="confirmPassword"
                v-model="confirmPassword"
                :type="showConfirmPassword ? 'text' : 'password'"
                class="w-full rounded-lg border px-3 py-2.5 text-sm text-foreground placeholder-foreground/40 transition-all duration-200 hover:border-primary/50 focus:outline-none focus:ring-2 bg-card/50"
                :class="[
                  errors.confirmPassword 
                    ? 'border-red-500/50 focus:border-red-500 focus:ring-red-500/20' 
                    : 'border-border focus:border-primary focus:ring-primary/20'
                ]"
                placeholder="••••••••"
                :disabled="isLoading"
                @blur="touched.confirmPassword = true"
              />
              <button
                type="button"
                @click="toggleConfirmPasswordVisibility"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-foreground/60 transition-colors hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 rounded-md p-1"
                :disabled="isLoading"
              >
                <component :is="showConfirmPassword ? EyeOff : Eye" :size="18" />
              </button>
            </div>
            <p v-if="errors.confirmPassword" class="text-xs text-red-400 animate-fade-in-up">
              {{ errors.confirmPassword }}
            </p>
          </div>

          <!-- Terms and conditions -->
          <div class="flex items-start">
            <label class="flex cursor-pointer items-start space-x-2">
              <input
                v-model="acceptTerms"
                type="checkbox"
                class="mt-0.5 h-3.5 w-3.5 rounded border-border bg-card/50 text-primary transition-colors focus:ring-2 focus:ring-primary/20 focus:ring-offset-0 focus:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50"
                :class="[
                  errors.acceptTerms 
                    ? 'border-red-500/50 ring-red-500/20' 
                    : 'border-border'
                ]"
                :disabled="isLoading"
                @blur="touched.acceptTerms = true"
              />
              <span class="text-xs text-foreground/60">
                I agree to the 
                <a href="#" class="text-primary hover:text-primary/80">Terms</a> 
                and 
                <a href="#" class="text-primary hover:text-primary/80">Privacy</a>
              </span>
            </label>
          </div>
          <p v-if="errors.acceptTerms" class="text-xs text-red-400 animate-fade-in-up">
            {{ errors.acceptTerms }}
          </p>

          <!-- Sign up button -->
          <button
            type="submit"
            :disabled="isLoading || !isFormValid"
            class="relative w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-2 focus:ring-offset-background disabled:cursor-not-allowed disabled:opacity-70 overflow-hidden group"
          >
            <span :class="{ 'opacity-0': isLoading }">
              Sign up
            </span>
            
            <!-- Loading spinner -->
            <div
              v-if="isLoading"
              class="absolute inset-0 flex items-center justify-center"
            >
              <svg 
                class="h-4 w-4 animate-spin text-white" 
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

        <!-- Footer with sign in link -->
        <div class="text-center text-xs text-foreground/60">
          <span>Already have an account? </span>
          <router-link
            to="/login"
            class="font-medium text-primary transition-colors hover:text-primary/80 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2 focus:ring-offset-background"
          >
            Sign in
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { 
  Code2, Globe, Database, Server, Hash, Eye,  EyeOff,
  Terminal, Cpu, Binary, GitBranch, Braces, Cloud, Box,
  Wrench, Workflow, Shield, Zap, Layers, Sparkles
} from 'lucide-vue-next'

// Form state
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const username = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const acceptTerms = ref(false)

// Loading state
const isLoading = ref(false)

// Validation state - track which fields have been touched
const touched = ref({
  username: false,
  email: false,
  password: false,
  confirmPassword: false,
  acceptTerms: false
})

// State to control left panel visibility
const showLeftPanel = ref(true)
const BREAKPOINT = 1024

// Username validation
const usernameError = computed(() => {
  if (!touched.value.username) return ''
  if (!username.value) return 'Username is required'
  if (username.value.length < 3) return 'Username must be at least 3 characters'
  if (username.value.length > 20) return 'Username must be less than 20 characters'
  if (!/^[a-zA-Z0-9_]+$/.test(username.value)) return 'Username can only contain letters, numbers and underscores'
  return ''
})

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
  if (password.value.length < 8) return 'Password must be at least 8 characters'
  if (!/[A-Z]/.test(password.value)) return 'Password must contain at least one uppercase letter'
  if (!/[a-z]/.test(password.value)) return 'Password must contain at least one lowercase letter'
  if (!/[0-9]/.test(password.value)) return 'Password must contain at least one number'
  return ''
})

// Confirm password validation
const confirmPasswordError = computed(() => {
  if (!touched.value.confirmPassword) return ''
  if (!confirmPassword.value) return 'Please confirm your password'
  if (confirmPassword.value !== password.value) return 'Passwords do not match'
  return ''
})

// Terms validation
const acceptTermsError = computed(() => {
  if (!touched.value.acceptTerms) return ''
  if (!acceptTerms.value) return 'You must accept the Terms and Privacy Policy'
  return ''
})

// Consolidated errors object
const errors = computed(() => ({
  username: usernameError.value,
  email: emailError.value,
  password: passwordError.value,
  confirmPassword: confirmPasswordError.value,
  acceptTerms: acceptTermsError.value
}))

// Form validation
const isFormValid = computed(() => {
  return (
    !usernameError.value &&
    !emailError.value &&
    !passwordError.value &&
    !confirmPasswordError.value &&
    !acceptTermsError.value &&
    username.value &&
    email.value &&
    password.value &&
    confirmPassword.value &&
    acceptTerms.value
  )
})

// Array of icons for background pattern
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

// Function to check window width
const checkWindowSize = () => {
  showLeftPanel.value = window.innerWidth >= BREAKPOINT
}

// Toggle password visibility
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

// Toggle confirm password visibility
const toggleConfirmPasswordVisibility = () => {
  showConfirmPassword.value = !showConfirmPassword.value
}

// Form submit handler
const handleSubmit = async (e: Event) => {
  e.preventDefault()
  
  // Mark all fields as touched to show validation errors
  touched.value = {
    username: true,
    email: true,
    password: true,
    confirmPassword: true,
    acceptTerms: true
  }
  
  if (!isFormValid.value) {
    return
  }
  
  isLoading.value = true
  
  try {
    // API call simulation - Replace with real logic
    await new Promise(resolve => setTimeout(resolve, 1000))
    console.log('Registration attempt:', { 
      username: username.value,
      email: email.value, 
      password: password.value,
      acceptTerms: acceptTerms.value
    })
    
    // Redirect to login or dashboard
    // router.push('/login')
    
  } finally {
    isLoading.value = false
  }
}

// Lifecycle hooks
onMounted(() => {
  checkWindowSize()
  window.addEventListener('resize', checkWindowSize)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkWindowSize)
})
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