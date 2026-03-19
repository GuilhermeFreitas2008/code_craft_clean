<!-- resources/js/pages/logged/profile/SettingsDisplay.vue -->
<template>
  <div class="mx-auto max-w-4xl">
    <!-- Back Button com animação -->
    <button 
      @click="$emit('back')"
      class="mb-6 flex items-center space-x-2 text-foreground/60 hover:text-primary transition-all duration-300 group relative overflow-hidden"
    >
      <span class="absolute inset-0 bg-gradient-to-r from-primary/0 via-primary/10 to-primary/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></span>
      <ArrowLeft :size="20" class="group-hover:-translate-x-1 transition-transform duration-300" />
      <span>Back to Profile</span>
    </button>

    <!-- Profile Header com Tabs -->
    <ProfileHeader 
      :active-tab="'settings'"
      @tab-change="handleTabChange"
    />

    <!-- Settings Header com animação de fade -->
    <Transition
      enter-active-class="transition-all duration-500 ease-out"
      enter-from-class="opacity-0 translate-y-4"
      enter-to-class="opacity-100 translate-y-0"
    >
      <div class="mt-8 mb-6">
        <h2 class="text-2xl font-semibold text-foreground">Settings</h2>
        <p class="text-foreground/60 mt-1">Manage your account settings and preferences</p>
      </div>
    </Transition>

    <!-- Settings Grid com animação de stagger -->
    <div class="space-y-6">
      <!-- Account Settings -->
      <Transition
        enter-active-class="transition-all duration-500 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
      >
        <div class="bg-card rounded-xl border border-white/5 p-5 hover:border-primary/20 transition-all duration-300 hover:shadow-lg hover:shadow-primary/5 group">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <User :size="16" class="text-primary" />
            </div>
            <div>
              <h3 class="text-base font-semibold text-foreground">Account Settings</h3>
              <p class="text-xs text-foreground/40">Update your personal information</p>
            </div>
          </div>

          <form @submit.prevent="saveAccountSettings" class="space-y-3">
            <!-- Name -->
            <div class="group/input">
              <label class="block text-xs font-medium text-foreground/80 mb-1 transition-colors duration-300 group-hover/input:text-primary">Name</label>
              <input
                v-model="accountForm.name"
                type="text"
                class="w-full px-3 py-2 text-sm rounded-lg bg-white/5 border border-white/10 focus:border-primary focus:outline-none transition-all duration-300 text-foreground hover:border-primary/30"
                placeholder="Your name"
              />
            </div>

            <!-- Email (não editável) -->
            <div>
              <label class="block text-xs font-medium text-foreground/80 mb-1">Email</label>
              <div class="w-full px-3 py-2 text-sm rounded-lg bg-white/5 border border-white/10 text-foreground/60 flex items-center justify-between">
                <span>{{ accountForm.email }}</span>
                <span class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded-full">Verified</span>
              </div>
              <p class="text-xs text-foreground/40 mt-1">Email cannot be changed. Contact support if needed.</p>
            </div>

            <!-- Password Change Section -->
            <div class="pt-3 mt-2 border-t border-white/5">
              <h4 class="text-sm font-medium text-foreground mb-3">Change Password</h4>
              
              <!-- Current Password -->
              <div class="mb-3">
                <label class="block text-xs font-medium text-foreground/80 mb-1">Current Password</label>
                <div class="relative">
                  <input
                    v-model="passwordForm.currentPassword"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    class="w-full px-3 py-2 text-sm rounded-lg bg-white/5 border border-white/10 focus:border-primary focus:outline-none transition-all duration-300 text-foreground pr-10"
                    placeholder="Enter current password"
                  />
                  <button
                    type="button"
                    @click="showCurrentPassword = !showCurrentPassword"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-foreground/40 hover:text-primary transition-colors"
                  >
                    <Eye v-if="!showCurrentPassword" :size="16" />
                    <EyeOff v-else :size="16" />
                  </button>
                </div>
              </div>

              <!-- New Password -->
              <div class="mb-3">
                <label class="block text-xs font-medium text-foreground/80 mb-1">New Password</label>
                <div class="relative">
                  <input
                    v-model="passwordForm.newPassword"
                    :type="showNewPassword ? 'text' : 'password'"
                    class="w-full px-3 py-2 text-sm rounded-lg bg-white/5 border border-white/10 focus:border-primary focus:outline-none transition-all duration-300 text-foreground pr-10"
                    placeholder="Enter new password"
                  />
                  <button
                    type="button"
                    @click="showNewPassword = !showNewPassword"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-foreground/40 hover:text-primary transition-colors"
                  >
                    <Eye v-if="!showNewPassword" :size="16" />
                    <EyeOff v-else :size="16" />
                  </button>
                </div>
              </div>

              <!-- Confirm New Password -->
              <div class="mb-2">
                <label class="block text-xs font-medium text-foreground/80 mb-1">Confirm New Password</label>
                <div class="relative">
                  <input
                    v-model="passwordForm.confirmPassword"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    class="w-full px-3 py-2 text-sm rounded-lg bg-white/5 border border-white/10 focus:border-primary focus:outline-none transition-all duration-300 text-foreground pr-10"
                    placeholder="Confirm new password"
                  />
                  <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-foreground/40 hover:text-primary transition-colors"
                  >
                    <Eye v-if="!showConfirmPassword" :size="16" />
                    <EyeOff v-else :size="16" />
                  </button>
                </div>
              </div>

              <!-- Password strength indicator -->
              <div v-if="passwordForm.newPassword" class="mb-3">
                <div class="flex justify-between text-xs mb-1">
                  <span class="text-foreground/40">Password strength</span>
                  <span :class="passwordStrength.color">{{ passwordStrength.text }}</span>
                </div>
                <div class="h-1 bg-white/10 rounded-full overflow-hidden">
                  <div 
                    class="h-full transition-all duration-300"
                    :class="passwordStrength.bgColor"
                    :style="{ width: passwordStrength.width + '%' }"
                  ></div>
                </div>
              </div>

              <!-- Password match indicator -->
              <div v-if="passwordForm.confirmPassword" class="text-xs mt-1" :class="passwordsMatch ? 'text-green-500' : 'text-red-400'">
                <span v-if="passwordsMatch">✓ Passwords match</span>
                <span v-else>✗ Passwords do not match</span>
              </div>
            </div>

            <!-- Save Button com animação -->
            <div class="flex justify-end pt-2">
              <button
                type="submit"
                class="px-4 py-1.5 text-sm bg-primary text-white rounded-lg hover:bg-primary/90 transition-all duration-300 transform hover:scale-105 flex items-center gap-2 group/btn relative overflow-hidden"
                :disabled="saving.account || !isPasswordFormValid"
              >
                <span class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/30 to-white/0 translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700"></span>
                <span v-if="saving.account" class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                <span class="relative z-10 text-sm">{{ saving.account ? 'Saving...' : 'Save Changes' }}</span>
              </button>
            </div>
          </form>
        </div>
      </Transition>

      <!-- Preferences -->
      <Transition
        enter-active-class="transition-all duration-500 ease-out delay-100"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
      >
        <div class="bg-card rounded-xl border border-white/5 p-5 hover:border-primary/20 transition-all duration-300 hover:shadow-lg hover:shadow-primary/5 group">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <Settings :size="16" class="text-primary" />
            </div>
            <div>
              <h3 class="text-base font-semibold text-foreground">Preferences</h3>
              <p class="text-xs text-foreground/40">Customize your experience</p>
            </div>
          </div>

          <div class="space-y-3">
            <!-- Language - com modal (apenas estético por agora) -->
            <div>
              <label class="block text-xs font-medium text-foreground/80 mb-2">Language</label>
              <button
                @click="openLanguageModal"
                class="w-full flex items-center justify-between p-3 rounded-lg bg-white/5 border border-white/10 hover:border-primary/30 transition-all duration-300 group/btn relative overflow-hidden"
              >
                <span class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700"></span>
                <div class="flex items-center gap-3">
                  <span class="text-2xl">{{ currentLanguageFlag }}</span>
                  <div class="text-left">
                    <span class="text-sm font-medium text-foreground">{{ currentLanguageName }}</span>
                    <p class="text-xs text-foreground/40">{{ currentLanguageNative }}</p>
                  </div>
                </div>
                <ChevronRight :size="16" class="text-foreground/40 group-hover/btn:translate-x-1 transition-transform duration-300" />
              </button>
            </div>

            <!-- Theme -->
            <div>
              <label class="block text-xs font-medium text-foreground/80 mb-2">Theme</label>
              <div class="grid grid-cols-3 gap-2">
                <button
                  v-for="(theme, index) in themes"
                  :key="theme.value"
                  @click="themeStore.setTheme(theme.value as ThemeMode)"
                  class="flex flex-col items-center gap-1 p-2 rounded-lg border-2 transition-all duration-300 relative overflow-hidden group/theme"
                  :class="themeStore.themeMode === theme.value 
                    ? 'border-primary bg-primary/10 text-primary' 
                    : 'border-white/10 text-foreground/60 hover:border-primary/30 hover:bg-white/5'"
                  :style="{ transitionDelay: `${index * 50}ms` }"
                >
                  <span class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent translate-x-[-100%] group-hover/theme:translate-x-[100%] transition-transform duration-700"></span>
                  <component :is="theme.icon" :size="18" class="relative z-10 group-hover/theme:scale-110 transition-transform duration-300" />
                  <span class="text-xs relative z-10">{{ theme.label }}</span>
                </button>
              </div>
              <p class="text-xs text-foreground/40 mt-1 text-center">
                Current: {{ themeStore.themeMode }}
              </p>
            </div>

            <!-- Notifications (apenas estético por agora) -->
            <div>
              <label class="block text-xs font-medium text-foreground/80 mb-2">Notifications</label>
              <label class="flex items-center justify-between p-2 rounded-lg bg-white/5 cursor-pointer hover:bg-white/10 transition-all duration-300 group/notify relative overflow-hidden">
                <span class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent translate-x-[-100%] group-hover/notify:translate-x-[100%] transition-transform duration-700"></span>
                <div class="relative z-10">
                  <span class="text-xs text-foreground">Email Notifications</span>
                  <p class="text-xs text-foreground/40">Receive updates about your courses</p>
                </div>
                <button
                  @click="preferences.emailNotifications = !preferences.emailNotifications"
                  class="relative w-10 h-5 rounded-full transition-all duration-300 flex-shrink-0 z-10"
                  :class="preferences.emailNotifications ? 'bg-primary' : 'bg-white/10'"
                >
                  <span 
                    class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white transition-all duration-300"
                    :class="preferences.emailNotifications ? 'transform translate-x-5' : ''"
                  />
                </button>
              </label>
            </div>

            <!-- Save Preferences Button -->
            <div class="flex justify-end pt-2">
              <button
                @click="savePreferences"
                class="px-4 py-1.5 text-sm bg-primary text-white rounded-lg hover:bg-primary/90 transition-all duration-300 transform hover:scale-105 flex items-center gap-2 group/btn relative overflow-hidden"
                :disabled="saving.preferences"
              >
                <span class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/30 to-white/0 translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700"></span>
                <span v-if="saving.preferences" class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                <span class="relative z-10 text-sm">{{ saving.preferences ? 'Saving...' : 'Save Preferences' }}</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Session & Danger Zone - Lado a lado -->
      <Transition
        enter-active-class="transition-all duration-500 ease-out delay-150"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <!-- Logout Section -->
          <div class="bg-card rounded-xl border border-white/5 p-5 hover:border-primary/20 transition-all duration-300 hover:shadow-lg hover:shadow-primary/5 group">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <LogOut :size="16" class="text-primary" />
              </div>
              <div>
                <h3 class="text-base font-semibold text-foreground">Session</h3>
                <p class="text-xs text-foreground/40">Manage your current session</p>
              </div>
            </div>

            <div class="flex items-center justify-between p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-all duration-300 group/item relative overflow-hidden">
              <span class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent translate-x-[-100%] group-hover/item:translate-x-[100%] transition-transform duration-700"></span>
              <div class="relative z-10">
                <h4 class="text-sm font-medium text-foreground">Logout</h4>
                <p class="text-xs text-foreground/40">Sign out</p>
              </div>
              <button
                @click="handleLogout"
                :disabled="userStore.isLoading"
                class="px-3 py-1 text-xs bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 transition-all duration-300 transform hover:scale-105 flex items-center gap-1 relative z-10"
              >
                <LogOut v-if="!userStore.isLoading" :size="14" class="group-hover/item:-translate-x-1 transition-transform duration-300" />
                <span v-else class="h-3 w-3 animate-spin rounded-full border-2 border-red-400 border-t-transparent"></span>
                <span>{{ userStore.isLoading ? '...' : 'Logout' }}</span>
              </button>
            </div>
          </div>

          <!-- Danger Zone -->
          <div class="bg-card rounded-xl border border-red-500/20 p-5 hover:border-red-500/30 transition-all duration-300 hover:shadow-lg hover:shadow-red-500/5 group">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <AlertCircle :size="16" class="text-red-400" />
              </div>
              <div>
                <h3 class="text-base font-semibold text-red-400">Danger Zone</h3>
                <p class="text-xs text-foreground/40">Irreversible actions</p>
              </div>
            </div>

            <div class="flex items-center justify-between p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-all duration-300 group/item relative overflow-hidden">
              <span class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent translate-x-[-100%] group-hover/item:translate-x-[100%] transition-transform duration-700"></span>
              <div class="relative z-10">
                <h4 class="text-sm font-medium text-foreground">Delete Account</h4>
                <p class="text-xs text-foreground/40">Permanent</p>
              </div>
              <button
                @click="openDeleteModal"
                class="px-3 py-1 text-xs bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 transition-all duration-300 transform hover:scale-105 flex items-center gap-1 relative z-10"
              >
                <Trash2 :size="14" class="group-hover/item:scale-110 transition-transform duration-300" />
                <span>Delete</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Language Selection Modal -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showLanguageModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeLanguageModal"></div>
          
          <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div class="relative bg-card border border-white/10 rounded-xl max-w-lg w-full p-6 shadow-2xl">
              <!-- Header -->
              <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                    <Globe :size="20" class="text-primary" />
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-foreground">Select Language</h3>
                    <p class="text-sm text-foreground/40">Choose your preferred language</p>
                  </div>
                </div>
                <button 
                  @click="closeLanguageModal"
                  class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 transition-colors flex items-center justify-center"
                >
                  <X :size="16" class="text-foreground/60" />
                </button>
              </div>

              <!-- Language List -->
              <div class="space-y-2 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                <button
                  v-for="lang in languages"
                  :key="lang.code"
                  @click="selectLanguage(lang.code)"
                  class="w-full flex items-center justify-between p-4 rounded-lg border-2 transition-all duration-300 group relative overflow-hidden"
                  :class="preferences.language === lang.code 
                    ? 'border-primary bg-primary/5' 
                    : 'border-white/10 hover:border-primary/30 hover:bg-white/5'"
                >
                  <span class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></span>
                  
                  <div class="flex items-center gap-4">
                    <span class="text-3xl">{{ lang.flag }}</span>
                    <div class="text-left">
                      <span class="text-base font-medium text-foreground block">{{ lang.name }}</span>
                      <span class="text-sm text-foreground/40">{{ lang.native }}</span>
                    </div>
                  </div>

                  <div v-if="preferences.language === lang.code" class="relative">
                    <div class="w-6 h-6 rounded-full bg-primary flex items-center justify-center">
                      <Check :size="14" class="text-white" />
                    </div>
                  </div>
                </button>
              </div>

              <!-- Footer -->
              <div class="mt-6 pt-4 border-t border-white/5 flex justify-end">
                <button
                  @click="closeLanguageModal"
                  class="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors"
                >
                  Done
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- Delete Account Confirmation Modal -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="!isDeleting && closeDeleteModal()"></div>
        
        <Transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div class="relative bg-background border border-red-500/20 rounded-xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 rounded-full bg-red-500/10 flex items-center justify-center animate-pulse">
                <AlertCircle class="w-5 h-5 text-red-500" />
              </div>
              <h3 class="text-lg font-semibold text-foreground">Delete Account</h3>
            </div>
            
            <p class="text-foreground/80 mb-4">
              This action <span class="text-red-400 font-semibold">cannot</span> be undone.
            </p>

            <div class="bg-red-500/5 border border-red-500/10 rounded-lg p-4 mb-6">
              <p class="text-xs text-foreground/60 mb-2">
                Type <span class="font-mono text-red-400 font-semibold">delete my account</span>
              </p>
              <input
                v-model="deleteConfirmation"
                type="text"
                class="w-full px-3 py-2 text-sm rounded-lg bg-white/5 border border-red-500/20 focus:border-red-500 focus:outline-none transition-all duration-300 text-foreground"
                placeholder="delete my account"
                :disabled="isDeleting"
              />
            </div>
            
            <div class="flex justify-end gap-3">
              <button 
                @click="closeDeleteModal" 
                class="px-4 py-2 text-sm rounded-lg text-foreground/60 hover:text-foreground hover:bg-white/5 transition-all duration-300" 
                :disabled="isDeleting"
              >
                Cancel
              </button>
              <button 
                @click="confirmDelete" 
                class="px-4 py-2 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all duration-300 flex items-center gap-2" 
                :disabled="isDeleting || deleteConfirmation !== 'delete my account'"
              >
                <span v-if="isDeleting" class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                <span v-else>Delete</span>
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  ArrowLeft, User, Settings, Sun, Moon, Monitor,
  LogOut, AlertCircle, Trash2, Globe, ChevronRight, X, Check,
  Eye, EyeOff
} from 'lucide-vue-next'
import ProfileHeader from '@/components/profile/ProfileHeader.vue'
import { useUserStore } from '@/stores/userStore'
import { useThemeStore, type ThemeMode } from '@/stores/themeStore'
import api from '@/services/axios'

const emit = defineEmits<{
  (e: 'back'): void
}>()

const router = useRouter()
const userStore = useUserStore()
const themeStore = useThemeStore()

// Languages data
const languages = [
  { code: 'en', name: 'English', native: 'English', flag: '🇺🇸' },
  { code: 'pt', name: 'Portuguese', native: 'Português', flag: '🇵🇹' },
  { code: 'es', name: 'Spanish', native: 'Español', flag: '🇪🇸' },
  { code: 'fr', name: 'French', native: 'Français', flag: '🇫🇷' },
  { code: 'de', name: 'German', native: 'Deutsch', flag: '🇩🇪' },
  { code: 'it', name: 'Italian', native: 'Italiano', flag: '🇮🇹' },
  { code: 'ja', name: 'Japanese', native: '日本語', flag: '🇯🇵' },
  { code: 'ko', name: 'Korean', native: '한국어', flag: '🇰🇷' },
  { code: 'zh', name: 'Chinese', native: '中文', flag: '🇨🇳' },
  { code: 'ru', name: 'Russian', native: 'Русский', flag: '🇷🇺' },
]

// Themes data
const themes = [
  { value: 'light', label: 'Light', icon: Sun },
  { value: 'dark', label: 'Dark', icon: Moon },
  { value: 'system', label: 'System', icon: Monitor },
]

// Modal states
const showLanguageModal = ref(false)

// Form states
const accountForm = ref({
  name: '',
  email: ''
})

const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// Password visibility states
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const preferences = ref({
  language: 'en',
  emailNotifications: true
})

const saving = ref({
  account: false,
  preferences: false
})

// Delete account states
const showDeleteModal = ref(false)
const isDeleting = ref(false)
const deleteConfirmation = ref('')

// Computed para idioma atual
const currentLanguage = computed(() => {
  return languages.find(l => l.code === preferences.value.language) || languages[0]
})

const currentLanguageName = computed(() => currentLanguage.value.name)
const currentLanguageNative = computed(() => currentLanguage.value.native)
const currentLanguageFlag = computed(() => currentLanguage.value.flag)

// Computed para validação de password
const passwordsMatch = computed(() => {
  return passwordForm.value.newPassword === passwordForm.value.confirmPassword
})

const passwordStrength = computed(() => {
  const pwd = passwordForm.value.newPassword
  if (!pwd) return { width: 0, text: 'None', color: 'text-foreground/40', bgColor: 'bg-white/10' }
  
  let strength = 0
  if (pwd.length >= 8) strength += 25
  if (pwd.match(/[a-z]/)) strength += 25
  if (pwd.match(/[A-Z]/)) strength += 25
  if (pwd.match(/[0-9]/)) strength += 25
  
  if (strength <= 25) return { width: 25, text: 'Weak', color: 'text-red-400', bgColor: 'bg-red-500' }
  if (strength <= 50) return { width: 50, text: 'Fair', color: 'text-yellow-500', bgColor: 'bg-yellow-500' }
  if (strength <= 75) return { width: 75, text: 'Good', color: 'text-blue-400', bgColor: 'bg-blue-500' }
  return { width: 100, text: 'Strong', color: 'text-green-500', bgColor: 'bg-green-500' }
})

const isPasswordFormValid = computed(() => {
  if (!passwordForm.value.currentPassword && !passwordForm.value.newPassword && !passwordForm.value.confirmPassword) {
    return true
  }
  return passwordForm.value.currentPassword && 
         passwordForm.value.newPassword && 
         passwordForm.value.confirmPassword && 
         passwordsMatch.value
})

const handleTabChange = (tab: 'profile' | 'settings') => {
  if (tab === 'profile') {
    router.push('/profile')
  }
}

// Language modal functions
const openLanguageModal = () => {
  showLanguageModal.value = true
}

const closeLanguageModal = () => {
  showLanguageModal.value = false
}

const selectLanguage = (code: string) => {
  preferences.value.language = code
  setTimeout(() => {
    closeLanguageModal()
  }, 200)
}

// Carregar dados do user e preferências
onMounted(async () => {
  accountForm.value.name = userStore.user?.name || ''
  accountForm.value.email = userStore.user?.email || ''
  
  // O tema é carregado automaticamente pela themeStore com prioridade BD > localStorage
})

// Save account settings (inclui password)
const saveAccountSettings = async () => {
  saving.value.account = true
  
  const data: any = {
    name: accountForm.value.name
  }
  
  if (passwordForm.value.currentPassword && passwordForm.value.newPassword) {
    data.current_password = passwordForm.value.currentPassword
    data.new_password = passwordForm.value.newPassword
    data.new_password_confirmation = passwordForm.value.confirmPassword
  }
  
  try {
    await new Promise(resolve => setTimeout(resolve, 1000))
    console.log('Saved data:', data)
    
    passwordForm.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    }
    
  } catch (error) {
    console.error('Error saving settings:', error)
  } finally {
    saving.value.account = false
  }
}

// Save preferences (guarda o tema atual na BD)
const savePreferences = async () => {
  saving.value.preferences = true
  
  // Guardar o tema atual na BD
  const result = await userStore.updatePreferences({
    theme: themeStore.themeMode
  })
  
  if (result.success) {
    console.log('✅ Tema guardado na BD:', themeStore.themeMode)
  } else {
    console.error('❌ Erro ao guardar tema:', result.error)
  }
  
  saving.value.preferences = false
}

// Logout
const handleLogout = async () => {
  await userStore.logout()
  router.push('/login')
}

// Delete account functions
const openDeleteModal = () => {
  showDeleteModal.value = true
  deleteConfirmation.value = ''
}

const closeDeleteModal = () => {
  if (isDeleting.value) return
  showDeleteModal.value = false
  deleteConfirmation.value = ''
}

const confirmDelete = async () => {
  if (deleteConfirmation.value !== 'delete my account') return
  
  isDeleting.value = true
  try {
    await api.delete('/user')
    await userStore.logout()
    router.push('/register')
  } catch (error) {
    console.error('Erro ao eliminar conta:', error)
  } finally {
    isDeleting.value = false
    showDeleteModal.value = false
  }
}
</script>

<style scoped>
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

* {
  transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

.bg-card:hover {
  transform: translateY(-1px);
}

.animate-slide-in {
  animation: slideIn 0.5s ease-out forwards;
}

.delay-100 { transition-delay: 100ms; }
.delay-150 { transition-delay: 150ms; }
.delay-200 { transition-delay: 200ms; }

/* Custom scrollbar para o modal */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}
</style>