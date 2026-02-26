
// CURSO
export interface ApiCourse {
  id: number
  title: string
  description: string
  slug: string
  created_at: string
  updated_at: string
  is_public: boolean
  is_draft: boolean
  created_by: number | null
  category_id: number
  difficulty_id: number
  category: ApiCategory
  difficulty: ApiDifficulty
  modules: ApiModule[]
  topics: ApiTopic[]
}

// CATEGORIA
export interface ApiCategory {
  id: number
  name: string
  created_at: string
  updated_at: string
}

// DIFICULDADE
export interface ApiDifficulty {
  id: number
  name: string
  created_at: string
  updated_at: string
}

// MÓDULO
export interface ApiModule {
  id: number
  course_id: number
  title: string
  slug: string
  description: string
  position: number
  created_at: string
  updated_at: string
  lessons: ApiLesson[]
}

// LIÇÃO
export interface ApiLesson {
  id: number
  module_id: number
  title: string
  slug: string
  content: string
  video_url: string | null
  position: number
  created_at: string
  updated_at: string
  // NOTA: completed NÃO vem da API, será adicionado depois
}

// TÓPICO (TAG)
export interface ApiTopic {
  id: number
  name: string
  slug: string
  created_at: string
  updated_at: string
  // A tabela pivot não vem na resposta
}

// ================================================
// TIPOS PARA ENROLLMENTS E PROGRESSO
// ================================================

// INSCRIÇÃO
export interface Enrollment {
  id: number
  user_id: number
  course_id: number
  enrolled_at: string
  created_at: string
  updated_at: string
}

// PROGRESSO INDIVIDUAL (lições completadas)
export interface UserProgress {
  id: number
  user_id: number
  lesson_id: number
  completed: boolean
  completed_at: string | null
  created_at: string
  updated_at: string
}

// PROGRESSO TOTAL DO CURSO
export interface UserCourseProgress {
  id: number
  user_id: number
  course_id: number
  progress_percent: number
  last_updated: string
  created_at: string
  updated_at: string
}

// ================================================
// TIPO FINAL QUE O COURSE DISPLAY ESPERA
// ================================================

export interface CourseDisplayProps {
  title: string
  description: string
  progressPercentage: number      // vem de progress_percent
  tags: string[]                  // vem de topics[].name
  lastUpdate: string              // updated_at formatado
  modules: {
    title: string
    lessons: {
      title: string
      completed: boolean           // vem do UserProgress
    }[]
  }[]
  difficulty: string               // difficulty.name
  category: string                 // category.name
}