<!-- components/lessons/VideoPlayer.vue -->
<template>
  <div 
    class="relative w-full overflow-hidden rounded-xl bg-gradient-to-br from-card/80 to-card border border-primary/20 shadow-2xl shadow-primary/5"
    :class="[aspectRatioClass]"
    @mouseenter="handleMouseEnter"
    @mouseleave="handleMouseLeave"
    @mousemove="handleMouseMove"
  >
    <!-- Loading State -->
    <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-card/90 z-10">
      <div class="flex flex-col items-center gap-4">
        <div class="relative">
          <div class="absolute inset-0 animate-ping rounded-full bg-primary/20"></div>
          <div class="relative flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-primary to-primary/50">
            <Play :size="28" class="text-white ml-1" />
          </div>
        </div>
        <div class="flex flex-col items-center gap-1">
          <span class="text-sm font-medium text-primary">CodeCraft</span>
          <span class="text-xs text-foreground/40">Loading your lesson...</span>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="absolute inset-0 flex items-center justify-center bg-card/90 z-10">
      <div class="flex flex-col items-center gap-4 text-center px-6 max-w-sm">
        <div class="relative">
          <div class="flex h-20 w-20 items-center justify-center rounded-full bg-red-500/10">
            <AlertCircle :size="36" class="text-red-400" />
          </div>
        </div>
        <div class="space-y-2">
          <h3 class="text-lg font-semibold text-foreground">Video unavailable</h3>
          <p class="text-sm text-foreground/60">{{ error }}</p>
        </div>
        <div class="flex gap-3 mt-2">
          <button 
            @click="retryLoad" 
            class="px-4 py-2 bg-primary/10 text-primary rounded-lg text-sm hover:bg-primary/20 transition-all duration-200 hover:scale-105"
          >
            Try Again
          </button>
          <a 
            v-if="videoId"
            :href="`https://youtu.be/${videoId}`" 
            target="_blank"
            class="px-4 py-2 bg-white/5 text-foreground rounded-lg text-sm hover:bg-white/10 transition-all duration-200 hover:scale-105 flex items-center gap-2"
          >
            <Youtube :size="18" class="text-red-500" />
            Watch on YouTube
          </a>
        </div>
      </div>
    </div>

    <!-- Thumbnail Overlay (antes de iniciar o vídeo) - CORRIGIDO -->
    <div v-if="!videoStarted" 
         class="absolute inset-0 cursor-pointer z-20"
         @click="startVideo"
    >
      <!-- Se existir thumbnail, mostra a imagem -->
      <img v-if="thumbnail" :src="thumbnail" alt="Video thumbnail" class="w-full h-full object-cover" />
      <!-- Se não existir thumbnail, mostra um fundo escuro com gradiente -->
      <div v-else class="w-full h-full bg-gradient-to-br from-gray-900 to-gray-800"></div>
      
      <!-- Overlay com botão play (igual para ambos os casos) -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/40">
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="relative">
            <div class="absolute inset-0 animate-ping rounded-full bg-primary/30"></div>
            <div class="relative flex h-20 w-20 items-center justify-center rounded-full bg-primary text-white transition-transform hover:scale-110">
              <Play :size="36" class="ml-1" />
            </div>
          </div>
        </div>
        <div class="absolute bottom-4 left-4">
          <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/20">
              <Play :size="18" class="text-primary" />
            </div>
            <span class="text-sm font-medium text-white">Click to play</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Video Player -->
    <div v-show="videoStarted && !loading && !error" class="relative w-full h-full" ref="playerWrapper">
      <!-- YouTube Player Container -->
      <div v-if="provider === 'youtube'" ref="playerContainer" class="w-full h-full"></div>
      
      <!-- Vimeo Player Container -->
      <div v-else-if="provider === 'vimeo'" ref="playerContainer" class="w-full h-full"></div>
      
      <!-- Direct Video File -->
      <video
        v-else-if="provider === 'direct'"
        ref="videoElement"
        class="w-full h-full"
        :src="videoUrl"
        :poster="thumbnail || ''"
        @play="onPlay"
        @pause="onPause"
        @ended="onEnded"
        @timeupdate="onTimeUpdate"
        @loadedmetadata="onLoadedMetadata"
      >
        <source :src="videoUrl" :type="getVideoType(videoUrl)" />
      </video>

      <!-- Overlay com controles CodeCraft -->
      <div 
        class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/30 transition-opacity duration-300 pointer-events-none"
        :class="showControlsOverlay ? 'opacity-100' : 'opacity-0'"
      >
        <!-- Botão central de play/pause (clicável) -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
          <button 
            @click.stop="togglePlay"
            class="flex h-20 w-20 items-center justify-center rounded-full bg-primary/90 text-white transition-all duration-200 hover:scale-110 hover:bg-primary shadow-xl shadow-primary/20 pointer-events-auto"
          >
            <Play v-if="!isPlaying" :size="32" class="ml-1" />
            <Pause v-else :size="32" />
          </button>
        </div>

        <!-- Barra inferior de controles (clicável) -->
        <div class="absolute bottom-0 left-0 right-0 p-6 pointer-events-none">
          <div class="flex flex-col gap-3 pointer-events-auto">
            <!-- Barra de progresso -->
            <div class="w-full h-1.5 bg-white/20 rounded-full cursor-pointer group/progress"
                 @click.stop="seekTo($event)">
              <div 
                class="h-full bg-primary rounded-full transition-all duration-100 relative"
                :style="{ width: `${progress}%` }"
              >
                <div class="absolute right-0 top-1/2 h-4 w-4 -translate-y-1/2 translate-x-1/2 rounded-full bg-primary opacity-0 group-hover/progress:opacity-100 transition-opacity"></div>
              </div>
            </div>

            <!-- Tempo -->
            <div class="flex justify-between items-center">
              <span class="text-sm text-white/60">{{ formatTime(currentTime) }}</span>
              <span class="text-sm text-white/60">{{ formatTime(duration) }}</span>
            </div>

            <!-- Botões de controle com full screen à direita -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <button @click.stop="togglePlay" class="text-white hover:text-primary transition-colors">
                  <Play v-if="!isPlaying" :size="22" />
                  <Pause v-else :size="22" />
                </button>
                
                <!-- Controle de Volume com Slider -->
                <div class="relative group/volume flex items-center">
                  <button @click.stop="toggleMute" class="text-white hover:text-primary transition-colors flex items-center justify-center">
                    <Volume2 v-if="!isMuted" :size="20" />
                    <VolumeX v-else :size="20" />
                  </button>
                  
                  <!-- Slider de Volume (aparece ao hover) -->
                  <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-8 h-24 bg-card/90 rounded-lg p-2 opacity-0 group-hover/volume:opacity-100 transition-opacity duration-200 pointer-events-auto hidden sm:block">
                    <div class="relative w-1 h-full bg-white/20 rounded-full mx-auto cursor-pointer"
                         @click="handleVolumeSliderClick">
                      <div 
                        class="absolute bottom-0 left-0 w-full bg-primary rounded-full"
                        :style="{ height: `${isMuted ? 0 : volume}%` }"
                      ></div>
                      <div 
                        class="absolute left-1/2 -translate-x-1/2 w-3 h-3 bg-primary rounded-full cursor-pointer"
                        :style="{ bottom: `calc(${isMuted ? 0 : volume}% - 6px)` }"
                        @mousedown="startVolumeDrag"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>

              <button @click.stop="toggleFullscreen" class="text-white hover:text-primary transition-colors">
                <Maximize :size="22" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { Play, Pause, Volume2, VolumeX, Maximize, AlertCircle, Youtube } from 'lucide-vue-next'

const props = defineProps<{
  src: string
  provider?: 'youtube' | 'vimeo' | 'direct'
  thumbnail?: string | null
  autoplay?: boolean
  aspectRatio?: '16:9' | '4:3' | '1:1'
}>()

const emit = defineEmits<{
  (e: 'play'): void
  (e: 'pause'): void
  (e: 'ended'): void
  (e: 'timeupdate', currentTime: number): void
  (e: 'loaded'): void
}>()

// ================================================
// STATE
// ================================================
const loading = ref(true)
const error = ref<string | null>(null)
const playerContainer = ref<HTMLElement | null>(null)
const playerWrapper = ref<HTMLElement | null>(null)
const videoElement = ref<HTMLVideoElement | null>(null)
const isPlaying = ref(false)
const isMuted = ref(false)
const volume = ref(100)
const previousVolume = ref(100)
const currentTime = ref(0)
const duration = ref(0)
const progress = ref(0)
const videoId = ref('')
const videoStarted = ref(false)
const isDraggingVolume = ref(false)
const showControlsOverlay = ref(true)
const isFullscreen = ref(false)
const hideTimer = ref<ReturnType<typeof setTimeout> | null>(null)

let youtubePlayer: any = null
let vimeoPlayer: any = null
let updateInterval: any = null

// ================================================
// COMPUTED
// ================================================
const videoUrl = computed(() => props.src)
const provider = computed(() => props.provider || detectProvider(props.src))

const aspectRatioClass = computed(() => {
  switch (props.aspectRatio) {
    case '4:3': return 'aspect-4/3'
    case '1:1': return 'aspect-square'
    default: return 'aspect-video'
  }
})

// ================================================
// FULLSCREEN DETECTION
// ================================================
const checkFullscreen = () => {
  isFullscreen.value = !!document.fullscreenElement
  
  if (isFullscreen.value) {
    showControlsOverlay.value = true
    startHideTimer()
  } else {
    showControlsOverlay.value = true
    cancelHideTimer()
  }
}

// ================================================
// TIMER CONTROLS
// ================================================
const startHideTimer = () => {
  if (hideTimer.value) {
    clearTimeout(hideTimer.value)
  }
  
  if (isFullscreen.value) {
    hideTimer.value = setTimeout(() => {
      showControlsOverlay.value = false
      hideTimer.value = null
    }, 3000)
  }
}

const cancelHideTimer = () => {
  if (hideTimer.value) {
    clearTimeout(hideTimer.value)
    hideTimer.value = null
  }
}

// ================================================
// CONTROLS VISIBILITY
// ================================================
const handleMouseEnter = () => {
  showControlsOverlay.value = true
  if (isFullscreen.value) {
    startHideTimer()
  }
}

const handleMouseLeave = () => {
  if (!isFullscreen.value) {
    showControlsOverlay.value = false
  } else {
    startHideTimer()
  }
}

const handleMouseMove = () => {
  showControlsOverlay.value = true
  if (isFullscreen.value) {
    startHideTimer()
  }
}

// ================================================
// METHODS
// ================================================
const detectProvider = (url: string): 'youtube' | 'vimeo' | 'direct' => {
  if (url.includes('youtube.com') || url.includes('youtu.be') || url.includes('youtube.com/embed')) {
    return 'youtube'
  } else if (url.includes('vimeo.com')) {
    return 'vimeo'
  } else {
    return 'direct'
  }
}

const getVideoType = (url: string): string => {
  const extension = url.split('.').pop()?.toLowerCase()
  switch (extension) {
    case 'mp4': return 'video/mp4'
    case 'webm': return 'video/webm'
    case 'ogg': return 'video/ogg'
    case 'mov': return 'video/quicktime'
    default: return 'video/mp4'
  }
}

const formatTime = (seconds: number): string => {
  if (isNaN(seconds)) return '0:00'
  const mins = Math.floor(seconds / 60)
  const secs = Math.floor(seconds % 60)
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

// ================================================
// VOLUME CONTROL
// ================================================
const setVolume = (value: number) => {
  const newVolume = Math.max(0, Math.min(100, value))
  volume.value = newVolume
  
  if (provider.value === 'direct' && videoElement.value) {
    videoElement.value.volume = newVolume / 100
    if (newVolume > 0) {
      videoElement.value.muted = false
      isMuted.value = false
    }
  } else if (provider.value === 'youtube' && youtubePlayer) {
    youtubePlayer.setVolume(newVolume)
    if (newVolume > 0) {
      isMuted.value = false
    }
  }
}

const handleVolumeSliderClick = (event: MouseEvent) => {
  const slider = event.currentTarget as HTMLElement
  const rect = slider.getBoundingClientRect()
  const clickY = event.clientY - rect.top
  const percentage = ((rect.height - clickY) / rect.height) * 100
  setVolume(percentage)
  if (isFullscreen.value) startHideTimer()
}

const startVolumeDrag = (event: MouseEvent) => {
  event.preventDefault()
  isDraggingVolume.value = true
  cancelHideTimer()
  
  const handleDrag = (e: MouseEvent) => {
    if (!isDraggingVolume.value) return
    
    const slider = document.querySelector('.group\\/volume .w-1') as HTMLElement
    if (!slider) return
    
    const rect = slider.getBoundingClientRect()
    const clickY = e.clientY - rect.top
    const percentage = ((rect.height - clickY) / rect.height) * 100
    setVolume(percentage)
  }
  
  const stopDrag = () => {
    isDraggingVolume.value = false
    document.removeEventListener('mousemove', handleDrag)
    document.removeEventListener('mouseup', stopDrag)
    if (isFullscreen.value) startHideTimer()
  }
  
  document.addEventListener('mousemove', handleDrag)
  document.addEventListener('mouseup', stopDrag)
}

const toggleMute = () => {
  if (isMuted.value) {
    setVolume(previousVolume.value)
    isMuted.value = false
    
    if (provider.value === 'direct' && videoElement.value) {
      videoElement.value.muted = false
    } else if (provider.value === 'youtube' && youtubePlayer) {
      youtubePlayer.unMute()
    }
  } else {
    previousVolume.value = volume.value
    setVolume(0)
    isMuted.value = true
    
    if (provider.value === 'direct' && videoElement.value) {
      videoElement.value.muted = true
    } else if (provider.value === 'youtube' && youtubePlayer) {
      youtubePlayer.mute()
    }
  }
  if (isFullscreen.value) startHideTimer()
}

// ================================================
// YOUTUBE PLAYER
// ================================================
const loadYouTubePlayer = () => {
  if (!playerContainer.value) return

  const url = props.src
  if (url.includes('youtube.com/watch')) {
    const urlParams = new URLSearchParams(new URL(url).search)
    videoId.value = urlParams.get('v') || ''
  } else if (url.includes('youtu.be/')) {
    videoId.value = url.split('youtu.be/')[1].split('?')[0]
  } else if (url.includes('youtube.com/embed/')) {
    videoId.value = url.split('embed/')[1].split('?')[0]
  }

  if (!videoId.value) {
    error.value = 'Invalid YouTube URL'
    loading.value = false
    return
  }

  if (!window.YT) {
    const tag = document.createElement('script')
    tag.src = 'https://www.youtube.com/iframe_api'
    const firstScriptTag = document.getElementsByTagName('script')[0]
    firstScriptTag.parentNode?.insertBefore(tag, firstScriptTag)

    window.onYouTubeIframeAPIReady = () => {
      createYouTubePlayer()
    }
  } else {
    createYouTubePlayer()
  }
}

const createYouTubePlayer = () => {
  if (!playerContainer.value) return

  youtubePlayer = new window.YT.Player(playerContainer.value, {
    videoId: videoId.value,
    playerVars: {
      autoplay: 0,
      controls: 0,
      modestbranding: 1,
      rel: 1,
      showinfo: 1,
      iv_load_policy: 1,
      disablekb: 0,
      fs: 0,
      playsinline: 1,
      origin: window.location.origin,
    },
    events: {
      onReady: onYouTubeReady,
      onStateChange: onYouTubeStateChange,
      onError: onYouTubeError,
    },
  })
}

const onYouTubeReady = () => {
  loading.value = false
  emit('loaded')
  duration.value = youtubePlayer.getDuration()
  youtubePlayer.setVolume(volume.value)
}

const onYouTubeStateChange = (event: any) => {
  const state = event.data
  if (state === window.YT.PlayerState.PLAYING) {
    isPlaying.value = true
    emit('play')
    
    if (updateInterval) clearInterval(updateInterval)
    
    updateInterval = setInterval(() => {
      if (youtubePlayer && isPlaying.value) {
        currentTime.value = youtubePlayer.getCurrentTime()
        progress.value = (currentTime.value / duration.value) * 100
      }
    }, 100)
  } else if (state === window.YT.PlayerState.PAUSED) {
    isPlaying.value = false
    emit('pause')
    if (updateInterval) clearInterval(updateInterval)
  } else if (state === window.YT.PlayerState.ENDED) {
    isPlaying.value = false
    emit('ended')
    if (updateInterval) clearInterval(updateInterval)
  }
}

const onYouTubeError = () => {
  error.value = 'This video cannot be played directly on this page'
  loading.value = false
}

// ================================================
// VIMEO PLAYER
// ================================================
const loadVimeoPlayer = () => {
  error.value = 'Vimeo player coming soon'
  loading.value = false
}

// ================================================
// DIRECT VIDEO PLAYER
// ================================================
const loadDirectPlayer = () => {
  if (!videoElement.value) return

  videoElement.value.volume = volume.value / 100

  videoElement.value.addEventListener('loadedmetadata', () => {
    duration.value = videoElement.value?.duration || 0
    loading.value = false
    emit('loaded')
  })

  videoElement.value.addEventListener('timeupdate', () => {
    if (videoElement.value) {
      currentTime.value = videoElement.value.currentTime
      progress.value = (currentTime.value / duration.value) * 100
      emit('timeupdate', currentTime.value)
    }
  })

  videoElement.value.addEventListener('play', () => {
    isPlaying.value = true
    emit('play')
  })

  videoElement.value.addEventListener('pause', () => {
    isPlaying.value = false
    emit('pause')
  })

  videoElement.value.addEventListener('ended', () => {
    isPlaying.value = false
    emit('ended')
  })

  videoElement.value.addEventListener('error', () => {
    error.value = 'Failed to load video'
    loading.value = false
  })

  if (props.autoplay) {
    videoElement.value.play().catch(() => {})
  }
}

// ================================================
// CONTROLS
// ================================================
const startVideo = () => {
  videoStarted.value = true
  setTimeout(() => {
    if (provider.value === 'youtube' && youtubePlayer) {
      youtubePlayer.playVideo()
    } else if (provider.value === 'direct' && videoElement.value) {
      videoElement.value.play()
    }
  }, 100)
}

const togglePlay = () => {
  if (provider.value === 'direct' && videoElement.value) {
    if (isPlaying.value) {
      videoElement.value.pause()
    } else {
      videoElement.value.play()
    }
  } else if (provider.value === 'youtube' && youtubePlayer) {
    if (isPlaying.value) {
      youtubePlayer.pauseVideo()
    } else {
      youtubePlayer.playVideo()
    }
  }
  if (isFullscreen.value) startHideTimer()
}

const toggleFullscreen = () => {
  const element = provider.value === 'direct' ? videoElement.value : playerWrapper.value
  if (!element) return

  if (!document.fullscreenElement) {
    if (element.requestFullscreen) {
      element.requestFullscreen()
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen()
    }
  }
}

const seekTo = (event: MouseEvent) => {
  if (provider.value === 'direct' && videoElement.value) {
    const progressBar = event.currentTarget as HTMLElement
    const rect = progressBar.getBoundingClientRect()
    const pos = (event.clientX - rect.left) / rect.width
    videoElement.value.currentTime = pos * duration.value
  } else if (provider.value === 'youtube' && youtubePlayer) {
    const progressBar = event.currentTarget as HTMLElement
    const rect = progressBar.getBoundingClientRect()
    const pos = (event.clientX - rect.left) / rect.width
    youtubePlayer.seekTo(pos * duration.value)
  }
  if (isFullscreen.value) startHideTimer()
}

const play = () => {
  if (provider.value === 'direct' && videoElement.value) {
    videoElement.value.play()
  } else if (provider.value === 'youtube' && youtubePlayer) {
    youtubePlayer.playVideo()
  }
}

const onPlay = () => {}
const onPause = () => {}
const onEnded = () => {}
const onTimeUpdate = () => {}
const onLoadedMetadata = () => {}

const retryLoad = () => {
  error.value = null
  loading.value = true
  videoStarted.value = false
  if (provider.value === 'youtube' && youtubePlayer) {
    youtubePlayer.playVideo()
  } else if (provider.value === 'direct' && videoElement.value) {
    videoElement.value.load()
  }
}

// ================================================
// WATCHERS
// ================================================
watch(() => props.src, () => {
  loading.value = true
  error.value = null
  videoStarted.value = false
  if (updateInterval) clearInterval(updateInterval)
  nextTick(() => {
    if (provider.value === 'youtube') {
      loadYouTubePlayer()
    } else if (provider.value === 'vimeo') {
      loadVimeoPlayer()
    } else {
      loadDirectPlayer()
    }
  })
})

// ================================================
// LIFECYCLE
// ================================================
onMounted(() => {
  if (provider.value === 'youtube') {
    loadYouTubePlayer()
  } else if (provider.value === 'vimeo') {
    loadVimeoPlayer()
  } else {
    loadDirectPlayer()
  }
  document.addEventListener('fullscreenchange', checkFullscreen)
})

onUnmounted(() => {
  if (updateInterval) clearInterval(updateInterval)
  if (youtubePlayer && youtubePlayer.destroy) {
    youtubePlayer.destroy()
  }
  if (hideTimer.value) {
    clearTimeout(hideTimer.value)
  }
  document.removeEventListener('fullscreenchange', checkFullscreen)
})
</script>

<style scoped>
@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes ping {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

.animate-ping {
  animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

.aspect-video {
  aspect-ratio: 16 / 9;
}

.aspect-4\/3 {
  aspect-ratio: 4 / 3;
}

.aspect-square {
  aspect-ratio: 1 / 1;
}

* {
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>