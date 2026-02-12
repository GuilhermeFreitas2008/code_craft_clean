<template>
  <div class="min-h-screen bg-background text-white">
    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 backdrop-blur-md bg-black/50 border-b border-border">
      <div class="max-w-7xl mx-auto pl-0 pr-2.5 py-4">
        <div class="flex items-center justify-between">
          <!-- Logo -->
          <div class="flex items-center gap-1">
            <img src="/images/Logo.svg" alt="CodeCraft Logo" class="w-14 h-14"/>
            <span class="text-xl font-bold">CodeCraft</span>
          </div>

          <!-- Desktop Menu -->
          <div class="hidden lg:flex items-center gap-8 ml-auto mr-15">
            <a href="#courses" @click.prevent="scrollToSection('courses')" class="hover:text-blue-400 transition">Courses</a>
            <a href="#features" @click.prevent="scrollToSection('features')" class="hover:text-blue-400 transition">Features</a>
            <a href="#about-us" @click.prevent="scrollToSection('about-us')" class="hover:text-blue-400 transition">About Us</a>
            <a href="#community" @click.prevent="scrollToSection('community')" class="hover:text-blue-400 transition">Community</a>
          </div>

          <!-- Auth Buttons -->
          <router-link to="/login" class="px-4 py-2 bg-blue-500 rounded-lg hover:bg-blue-700 transition">
            Sign In
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section id="hero" class="relative pt-32 overflow-hidden">
      <div class="absolute inset-0 bg-grid-white-large"></div>
      
      <div class="absolute top-0 inset-x-0 h-[600px] bg-gradient-to-b from-blue-500/10 via-blue-500/5 to-transparent"></div>

      <div class="relative z-10 pl-6 sm:pl-8 lg:pl-12 xl:pl-10 2xl:pl-16 pr-10">
        <div class="flex flex-col lg:flex-row min-h-[85vh] lg:items-start">
          
          <div class="lg:w-1/2 lg:pr-8 animate-fade-in-up">
            <div class="pt-2">
              <h1 class="text-7xl font-extrabold text-white leading-[0.9] tracking-tight max-w-xl">
                
                <span class="inline-flex items-baseline gap-3 whitespace-nowrap">
                  <span>Learn to code from</span>
                </span>
                
                <span class="inline-flex items-baseline gap-3 whitespace-nowrap">
                  <span>zero to</span>
                  <span class="text-blue-500">
                    professional
                  </span>
                </span>

              </h1>
            </div>

            <p class="text-xl text-gray-400 mb-6 max-w-lg leading-relaxed mt-6 animate-fade-in-up animation-delay-200">
              Discover the world of programming with practical courses and real projects. From fundamentals to advanced technologies.
            </p>

            <div class="flex flex-wrap gap-4 mb-6 animate-fade-in-up animation-delay-300">
              <router-link to="/login" class="px-10 py-5 text-lg bg-blue-500 rounded-xl flex items-center gap-3 font-semibold transition-all duration-300 hover:bg-blue-600 hover:scale-[1.03] hover:shadow-[0_10px_40px_rgba(59,130,246,0.10)] active:scale-[0.97]">
                Start Now
                <ArrowRight class="w-5 h-5" />
              </router-link>
            </div>

            <div class="flex flex-wrap gap-8 text-white animate-fade-in-up animation-delay-400">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center">
                  <Users class="w-6 h-6 text-blue-400" />
                </div>
                <div>
                  <div class="text-2xl font-bold">1000+</div>
                  <div class="text-sm text-gray-400">Students</div>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center">
                  <BookOpen class="w-6 h-6 text-blue-400" />
                </div>
                <div>
                  <div class="text-2xl font-bold">50+</div>
                  <div class="text-sm text-gray-400">Tutorials</div>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center">
                  <Headphones class="w-6 h-6 text-blue-400" />
                </div>
                <div>
                  <div class="text-2xl font-bold">24/7</div>
                  <div class="text-sm text-gray-400">Support</div>
                </div>
              </div>
            </div>
          </div>

          <div class="lg:w-5/12 lg:pl-6 lg:pt-0 mt-12 lg:mt-7 animate-fade-in-up animation-delay-500">
            <div class="relative group lg:translate-x-27">
              <div class="relative bg-gray-950/80 backdrop-blur-sm border border-border rounded-2xl p-1 -mt-2">

                <div class="flex items-center justify-between px-4 py-3 bg-[#161b22] border-b border-white/5 rounded-t-xl">
                  <div class="flex gap-2">
                    <div class="w-3 h-3 rounded-full bg-[#ff5f56]"></div>
                    <div class="w-3 h-3 rounded-full bg-[#ffbd2e]"></div>
                    <div class="w-3 h-3 rounded-full bg-[#27c93f]"></div>
                  </div>
                  <span class="text-xs text-gray-500 font-mono">script.js</span>
                </div>

                <div class="font-mono text-sm leading-relaxed p-4 space-y-1 min-h-[270px]">

                  <p
                    v-for="(line, index) in visibleLines"
                    :key="index"
                    class="whitespace-pre"
                  >
                    <span v-html="highlight(line)"></span>
                  </p>

                  <p class="whitespace-pre">
                    <span v-html="highlight(currentLine)"></span>
                    <span class="inline-block w-2 h-4 bg-blue-500 ml-1 animate-pulse"></span>
                  </p>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section "What you'll learn" -->
    <section id="courses" class="py-24 relative bg-[#020617]">
      <div class="max-w-7xl mx-auto px-4 sm:px-5 lg:px-6">
        <div class="text-center mb-16 space-y-4">
          <h2 class="text-4xl font-bold text-white fade-in-on-scroll">What you'll <span class="text-blue-500">learn</span></h2>
          <p class="text-gray-400 text-lg fade-in-on-scroll animation-delay-200">A complete path to the job market.</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="(course, index) in courses" :key="course.title" 
               :class="`bg-gray-900/50 border border-border rounded-2xl p-6 hover:bg-gray-900/80 transition h-full cursor-pointer fade-in-on-scroll animation-delay-${300 + (index % 3) * 100}`">
            <div class="space-y-4">
              <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center">
                <component :is="course.icon" class="w-6 h-6 text-blue-400" />
              </div>
              <div>
                <h3 class="text-xl font-bold text-white mb-3">{{ course.title }}</h3>
                <p class="text-gray-400 text-sm leading-relaxed">{{ course.description }}</p>
              </div>
              <div class="flex flex-wrap gap-2 pt-2">
                <span v-for="tag in course.tags" :key="tag" class="px-3 py-1 rounded-full bg-white/5 border border-white/5 text-xs text-gray-400">
                  {{ tag }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section "Why choose our platform" -->
    <section id="features" class="py-24 relative bg-[#020617]">
      <div class="max-w-4xl mx-auto px-4 sm:px-5 lg:px-6">
        <div class="text-center mb-16 space-y-4">
          <h2 class="text-4xl font-bold text-white fade-in-on-scroll">Why choose our <span class="text-blue-500">platform</span></h2>
          <p class="text-gray-400 text-lg fade-in-on-scroll animation-delay-200">Everything you need to succeed in programming.</p>
        </div>
        
        <div class="grid md:grid-cols-2 gap-8">
          <div v-for="(benefit, index) in benefits" :key="benefit.title" 
               :class="`flex items-start gap-4 p-6 rounded-xl bg-gray-900/30 hover:bg-gray-900/50 transition fade-in-on-scroll animation-delay-${300 + (index % 2) * 100}`">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center">
              <component :is="benefit.icon" class="w-6 h-6 text-blue-400" />
            </div>
            <div class="space-y-2">
              <h3 class="text-lg font-bold text-white">{{ benefit.title }}</h3>
              <p class="text-gray-400 text-sm">{{ benefit.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section "About Us" -->
    <section id="about-us" class="py-24 relative bg-[#020617]">
      <div class="max-w-6xl mx-auto px-4 sm:px-5 lg:px-6">
        <div class="text-center mb-16 space-y-4">
          <div class="flex justify-center fade-in-on-scroll">
            <div class="w-20 h-20 rounded-2xl bg-blue-500/10 flex items-center justify-center">
              <Target class="w-10 h-10 text-blue-400" />
            </div>
          </div>
          <h2 class="text-4xl font-bold text-white fade-in-on-scroll animation-delay-200">About <span class="text-blue-500">Us</span></h2>
          <p class="text-gray-400 text-lg max-w-3xl mx-auto fade-in-on-scroll animation-delay-300">
            We're passionate about making programming education accessible, practical, and career-focused for everyone.
          </p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
          <div class="bg-gray-900/30 border border-border rounded-2xl p-8 text-center space-y-6 hover:bg-gray-900/50 transition fade-in-on-scroll animation-delay-300">
            <div class="w-16 h-16 rounded-xl bg-blue-500/10 flex items-center justify-center mx-auto">
              <Rocket class="w-8 h-8 text-blue-400" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-white mb-3">Our Mission</h3>
              <p class="text-gray-400">
                To democratize programming education and help people worldwide build successful tech careers, regardless of their background.
              </p>
            </div>
          </div>
          
          <div class="bg-gray-900/30 border border-border rounded-2xl p-8 text-center space-y-6 hover:bg-gray-900/50 transition fade-in-on-scroll animation-delay-400">
            <div class="w-16 h-16 rounded-xl bg-blue-500/10 flex items-center justify-center mx-auto">
              <Heart class="w-8 h-8 text-blue-400" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-white mb-3">Our Values</h3>
              <p class="text-gray-400">
                Quality education, practical learning, community support, and continuous innovation drive everything we do.
              </p>
            </div>
          </div>
          
          <div class="bg-gray-900/30 border border-border rounded-2xl p-8 text-center space-y-6 hover:bg-gray-900/50 transition fade-in-on-scroll animation-delay-500">
            <div class="w-16 h-16 rounded-xl bg-blue-500/10 flex items-center justify-center mx-auto">
              <TrendingUp class="w-8 h-8 text-blue-400" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-white mb-3">Our Impact</h3>
              <p class="text-gray-400">
                Over 1,000 students transformed their careers, 50+ companies hiring our graduates, and a growing global community.
              </p>
            </div>
          </div>
        </div>
        
        <div class="mt-16 bg-gray-900/20 border border-border rounded-2xl p-8 fade-in-on-scroll animation-delay-600">
          <div class="grid md:grid-cols-2 gap-8 items-center">
            <div class="space-y-4">
              <h3 class="text-2xl font-bold text-white">Meet Our Team</h3>
              <p class="text-gray-400">
                We're a diverse team of experienced developers, educators, and industry professionals united by our passion for teaching and technology.
              </p>
              <div class="flex items-center gap-4 pt-4">
                <div class="w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center">
                  <Users class="w-6 h-6 text-blue-400" />
                </div>
                <div>
                  <div class="text-xl font-bold text-white">15+ Experts</div>
                  <div class="text-sm text-gray-400">Industry Professionals</div>
                </div>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center p-4 bg-white/5 rounded-xl fade-in-on-scroll animation-delay-700">
                <div class="text-2xl font-bold text-blue-400">5+</div>
                <div class="text-sm text-gray-400">Years Experience</div>
              </div>
              <div class="text-center p-4 bg-white/5 rounded-xl fade-in-on-scroll animation-delay-800">
                <div class="text-2xl font-bold text-blue-400">10+</div>
                <div class="text-sm text-gray-400">Countries Served</div>
              </div>
              <div class="text-center p-4 bg-white/5 rounded-xl fade-in-on-scroll animation-delay-900">
                <div class="text-2xl font-bold text-blue-400">95%</div>
                <div class="text-sm text-gray-400">Satisfaction Rate</div>
              </div>
              <div class="text-center p-4 bg-white/5 rounded-xl fade-in-on-scroll animation-delay-1000">
                <div class="text-2xl font-bold text-blue-400">24/7</div>
                <div class="text-sm text-gray-400">Team Support</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Community Section -->
    <section id="community" class="py-24 relative bg-[#020617]">
      <div class="max-w-4xl mx-auto px-4 sm:px-5 lg:px-6">
        <div class="bg-gradient-to-r from-blue-500/20 to-purple-500/20 border border-border rounded-2xl p-8 text-center space-y-8 fade-in-on-scroll">
          <div class="space-y-4">
            <div class="flex justify-center">
              <img src="/images/Discord-icon.svg" alt="Discord Community" class="w-16 h-16" />
            </div>
            
            <h2 class="text-4xl font-bold text-white">Join our <span class="text-blue-400">Community</span></h2>
            <p class="text-gray-300 text-lg max-w-2xl mx-auto">
              Connect with thousands of developers, get help with projects, share knowledge, and grow together in our active community.
            </p>
          </div>
          
          <div class="space-y-4">
            <div class="flex flex-wrap gap-4 justify-center">
              <router-link to="/login" class="px-8 py-4 bg-[#5865F2] rounded-xl flex items-center gap-3 font-medium transition-all duration-300 hover:bg-blue-600 hover:scale-[1.03] hover:shadow-[0_10px_40px_rgba(59,130,246,0.10)] active:scale-[0.97]">
                <img src="/images/Discord-icon.svg" alt="Discord" class="w-5 h-5" />
                Sign In to Join Us
              </router-link>
            </div>
          </div>
          
          <p class="text-gray-400 text-sm pt-4">Sign in to access our community features and start connecting!</p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black border-t border-border py-12">
      <div class="max-w-7xl mx-auto px-6">
        <div class="grid md:grid-cols-4 gap-8">
          <div class="space-y-4">
            <div class="flex items-center gap-2">
              <img src="/images/Logo.svg" alt="CodeCraft Logo" class="w-14 h-14"/>
              <span class="text-xl font-bold">Code Craft</span>
            </div>
            <p class="text-gray-400 text-sm">
              Learn programming from zero to professional with the best online courses.
            </p>
          </div>

          <div>
            <h3 class="font-bold text-white mb-4">Courses</h3>
            <ul class="space-y-2 text-gray-400">
              <li><a href="#" class="hover:text-blue-400 transition">Frontend</a></li>
              <li><a href="#" class="hover:text-blue-400 transition">Backend</a></li>
              <li><a href="#" class="hover:text-blue-400 transition">Mobile</a></li>
              <li><a href="#" class="hover:text-blue-400 transition">Data Science</a></li>
            </ul>
          </div>

          <div>
            <h3 class="font-bold text-white mb-4">Resources</h3>
            <ul class="space-y-2 text-gray-400">
              <li><a href="#" class="hover:text-blue-400 transition">Blog</a></li>
              <li><a href="#" class="hover:text-blue-400 transition">Tutorials</a></li>
              <li><a href="#" class="hover:text-blue-400 transition">Documentation</a></li>
              <li><a href="#" class="hover:text-blue-400 transition">Community</a></li>
            </ul>
          </div>

          <div>
            <h3 class="font-bold text-white mb-4">Legal</h3>
            <ul class="space-y-2 text-gray-400">
              <li><a href="#" class="hover:text-blue-400 transition">Terms of Service</a></li>
              <li><a href="#" class="hover:text-blue-400 transition">Privacy Policy</a></li>
              <li><a href="#" class="hover:text-blue-400 transition">Cookies</a></li>
              <li><a href="#" class="hover:text-blue-400 transition">FAQ</a></li>
            </ul>
          </div>
        </div>

        <div class="border-t border-border mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
          <p class="text-gray-400 text-sm">© 2024 CodeCraft. All rights reserved.</p>
          <div class="flex gap-4 mt-4 md:mt-0">
            <a href="#" class="text-gray-400 hover:text-white transition">
              <img src="/images/Discord-icon.svg" alt="CodeCraft Logo" class="w-5 h-5" />
            </a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { 
  ArrowRight, Users, BookOpen, Headphones,
  Monitor, Server, Globe, Smartphone, Palette, ChartBar,
  Video, FileCode, UserCircle, Award, Lock, MessageCircle,
  Target, Rocket, Heart, TrendingUp  // Ícones adicionados para About Us
} from 'lucide-vue-next'
import { ref, onMounted, onUnmounted } from 'vue'

// Função smooth scroll simplificada
const scrollToSection = (sectionId) => {
  const element = document.getElementById(sectionId)
  if (element) {
    // Altura da navbar
    const navbarHeight = 80
    const elementPosition = element.getBoundingClientRect().top
    const offsetPosition = elementPosition + window.pageYOffset - navbarHeight
    
    // Scroll suave nativo do browser
    window.scrollTo({
      top: offsetPosition,
      behavior: 'smooth'
    })
  }
}

// Configuração de animações
const observer = ref(null)

const initScrollAnimations = () => {
  observer.value = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-fade-in-up')
      }
    })
  }, {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  })

  // Observar todos os elementos com a classe fade-in-on-scroll
  document.querySelectorAll('.fade-in-on-scroll').forEach(el => {
    observer.value.observe(el)
  })
}

// Inicializar animações quando o componente for montado
onMounted(() => {
  // Adicionar classe inicial para elementos da hero section
  setTimeout(() => {
    const heroElements = document.querySelectorAll('.animate-fade-in-up')
    heroElements.forEach(el => {
      el.classList.add('animate-fade-in-up')
    })
  }, 100)

  // Inicializar observador de scroll
  setTimeout(initScrollAnimations, 500)

  // Animação de digitação
  typeLine()
})

// Limpar observador quando o componente for desmontado
onUnmounted(() => {
  if (observer.value) {
    observer.value.disconnect()
  }
})

const courses = [
  {
    title: 'Frontend',
    description: 'Development of modern and responsive web interfaces with the latest technologies.',
    icon: Monitor,
    tags: ['HTML/CSS', 'JavaScript', 'React', 'Vue']
  },
  {
    title: 'Backend',
    description: 'Development of servers, APIs and business logic for scalable web applications.',
    icon: Server,
    tags: ['Node.js', 'Python', 'SQL', 'API REST']
  },
  {
    title: 'DevOps',
    description: 'Deployment automation, monitoring and infrastructure for production applications.',
    icon: Globe,
    tags: ['Docker', 'AWS', 'CI/CD', 'Kubernetes']
  },
  {
    title: 'Mobile',
    description: 'Development of native and cross-platform mobile applications for iOS and Android.',
    icon: Smartphone,
    tags: ['React Native', 'Flutter', 'Swift', 'Kotlin']
  },
  {
    title: 'UI/UX Design',
    description: 'Interface design and user experience to create intuitive digital products.',
    icon: Palette,
    tags: ['Figma', 'Adobe XD', 'Prototyping', 'User Research']
  },
  {
    title: 'Data Science',
    description: 'Data analysis, machine learning and visualization to extract valuable insights.',
    icon: ChartBar,
    tags: ['Python', 'R', 'TensorFlow', 'Pandas']
  }
]

const benefits = [
  {
    title: 'Video Lessons',
    description: 'High-quality video lessons with detailed explanations and practical examples.',
    icon: Video
  },
  {
    title: 'Practical Projects',
    description: 'Apply what you learn in real projects that simulate market challenges.',
    icon: FileCode
  },
  {
    title: 'Community',
    description: 'Connect with other students and mentors to share knowledge.',
    icon: UserCircle
  },
  {
    title: 'Certificates',
    description: 'Obtain recognized certificates that validate your skills.',
    icon: Award
  },
  {
    title: 'Lifetime Access',
    description: 'Access the content forever, with regular updates included.',
    icon: Lock
  },
  {
    title: 'Mentoring',
    description: 'Individual sessions with experienced professionals for personalized guidance.',
    icon: MessageCircle
  }
]

// ANIMAÇÃO TYPING COM LOOP
const codeLines = [
  "// learn to code",
  "function learnToCode() {",
  "  const skills = [];",
  "  const dedication = true;",
  "",
  "  while (dedication) {",
  "    skills.push('knowledge');",
  "  }",
  "}"
]

// Estado
const visibleLines = ref([])
const currentLine = ref('')
let lineIndex = 0
let charIndex = 0

// Configuração
const typingSpeed = 35
const lineDelay = 400
const loopDelay = 3000

// Função principal
const typeLine = () => {
  if (lineIndex >= codeLines.length) {
    setTimeout(resetTyping, loopDelay)
    return
  }

  const line = codeLines[lineIndex]

  if (charIndex < line.length) {
    currentLine.value += line.charAt(charIndex)
    charIndex++
    setTimeout(typeLine, typingSpeed)
  } else {
    visibleLines.value.push(currentLine.value)
    currentLine.value = ''
    charIndex = 0
    lineIndex++
    setTimeout(typeLine, lineDelay)
  }
}

// Reset limpo (sem mexer no layout)
const resetTyping = () => {
  visibleLines.value = []
  currentLine.value = ''
  lineIndex = 0
  charIndex = 0
  typeLine()
}

// HIGHLIGHT DO CÓDIGO
const highlight = (line) => {
  return line
    .replace(/(function|const|while)/g, '<span class="text-purple-400">$1</span>')
    .replace(/(learnToCode|push)/g, '<span class="text-blue-400">$1</span>')
    .replace(/('knowledge')/g, '<span class="text-green-400">$1</span>')
    .replace(/(\/\/.*)/g, '<span class="text-gray-500">$1</span>')
}
</script>

<style scoped>
/* Estilos específicos da landing page */
.landing-hero {
  position: relative;
  padding-top: 8rem;
  overflow: hidden;
}

.code-window {
  position: relative;
  background: rgba(17, 24, 39, 0.8);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 1rem;
}

.code-window::before {
  content: '';
  position: absolute;
  inset: -1px;
  background: linear-gradient(45deg, #3b82f6, #8b5cf6);
  border-radius: 1rem;
  z-index: -1;
  opacity: 0.2;
  filter: blur(8px);
}

.course-card {
  background: rgba(17, 24, 39, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 1rem;
  transition: all 0.3s ease;
}

.course-card:hover {
  background: rgba(17, 24, 39, 0.8);
  transform: translateY(-4px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.benefit-card {
  background: rgba(17, 24, 39, 0.3);
  border-radius: 1rem;
  transition: all 0.3s ease;
}

.benefit-card:hover {
  background: rgba(17, 24, 39, 0.6);
}

.cta-section {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(139, 92, 246, 0.2));
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 1rem;
}

/* Animação específica do botão principal */
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}

/* Animações de fade in */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.6s ease-out forwards;
  opacity: 0;
}

/* Classes de delay para animação */
.animation-delay-200 { animation-delay: 200ms; }
.animation-delay-300 { animation-delay: 300ms; }
.animation-delay-400 { animation-delay: 400ms; }
.animation-delay-500 { animation-delay: 500ms; }
.animation-delay-600 { animation-delay: 600ms; }
.animation-delay-700 { animation-delay: 700ms; }
.animation-delay-800 { animation-delay: 800ms; }
.animation-delay-900 { animation-delay: 900ms; }
.animation-delay-1000 { animation-delay: 1000ms; }

/* Para elementos que aparecem no scroll */
.fade-in-on-scroll {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.fade-in-on-scroll.animate-fade-in-up {
  opacity: 1;
  transform: translateY(0);
}

/* Para elementos da hero section que aparecem na abertura */
.animate-fade-in-up {
  opacity: 0;
}

.animate-fade-in-up.animate-fade-in-up {
  animation: fadeInUp 0.6s ease-out forwards;
}

/* Otimizações de performance */
* {
  backface-visibility: hidden;
}

/* Ajustes para evitar que a navbar e footer sejam animados */
nav, footer, footer * {
  animation: none !important;
  opacity: 1 !important;
  transform: none !important;
}
</style>