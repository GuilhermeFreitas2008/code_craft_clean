export interface Resource {
  id: number
  title: string
  type: 'pdf' | 'image' | 'presentation' | 'archive' | 'other'
  size: string
  url: string
}

// Interface base (o que vem da API)
export interface Comment {
  id: number
  userId: number
  userName: string
  userInitials: string
  content: string
  createdAt: Date | string
  likes: number
  is_liked_by_user?: boolean           // <-- ADICIONADO
  parent_id?: number | null            // <-- ADICIONADO (para saber se é resposta)
  replies?: Comment[]                  // Comentários podem ter respostas
}

// Interface estendida para o Display (com propriedades UI)
export interface CommentWithLikeStatus extends Omit<Comment, 'replies' | 'is_liked_by_user' | 'parent_id'> {
  isLikedByCurrentUser: boolean
  replyToUserName?: string | null      // <-- ADICIONADO (para mostrar @ de quem responde)
  replies?: CommentWithLikeStatus[]    // Respostas também têm o status
}

export interface Lesson {
  id: number
  title: string
  content: string
  video_url: string | null
  thumbnail?: string | null
  position: number
  completed?: boolean
  resources?: Resource[]
}

export interface Module {
  id: number
  title: string
  position: number
  lessons: Lesson[]
}

export interface CourseWithModules {
  id: number
  title: string
  modules: Module[]
}

export interface LessonViewProps {
  currentLesson: Lesson
  allModules: Module[]
  onLessonSelect: (lessonId: number) => void
}