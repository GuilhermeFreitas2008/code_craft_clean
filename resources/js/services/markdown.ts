import MarkdownIt from 'markdown-it'
import hljs from 'highlight.js'
import 'highlight.js/styles/github-dark.css' // Opcional: podes escolher outro tema

// Configurar markdown-it com highlight.js
const md: MarkdownIt = new MarkdownIt({
  html: true,           // Permitir HTML no markdown
  linkify: true,        // Autoconverter links em clicáveis
  typographer: true,    // Substituições tipográficas (--- → —, etc.)
  highlight: function (str: string, lang: string): string {
    // Verificar se a linguagem é suportada pelo highlight.js
    if (lang && hljs.getLanguage(lang)) {
      try {
        // Fazer highlight do código
        const highlighted = hljs.highlight(str, { 
          language: lang,
          ignoreIllegals: true 
        }).value
        
        // Retornar HTML com classes específicas para estilização
        return `<pre class="code-block language-${lang}"><code class="hljs">${highlighted}</code></pre>`
      } catch (__) {
        // Fallback em caso de erro
        console.warn(`Erro ao fazer highlight da linguagem: ${lang}`)
      }
    }
    
    // Fallback para código sem linguagem especificada
    return `<pre class="code-block"><code>${md.utils.escapeHtml(str)}</code></pre>`
  }
})

// Adicionar regras personalizadas (opcional)
md.renderer.rules.image = (tokens, idx, options, env, self) => {
  const token = tokens[idx]
  const src = token.attrGet('src')
  const alt = token.content || 'Imagem'
  
  // Personalizar imagens com classes CSS
  return `<img src="${src}" alt="${alt}" class="rounded-lg max-w-full h-auto my-4" loading="lazy">`
}

md.renderer.rules.link_open = (tokens, idx, options, env, self) => {
  const token = tokens[idx]
  const href = token.attrGet('href')
  
  // Abrir links externos em nova aba
  if (href && (href.startsWith('http') || href.startsWith('https'))) {
    token.attrSet('target', '_blank')
    token.attrSet('rel', 'noopener noreferrer')
  }
  
  // Usar o renderizador padrão
  return self.renderToken(tokens, idx, options)
}

// ================================================
// Função principal para renderizar markdown
// ================================================
export const renderMarkdown = (content: string): string => {
  if (!content) return ''
  
  try {
    return md.render(content)
  } catch (error) {
    console.error('Erro ao renderizar markdown:', error)
    // Fallback seguro: escapar HTML e mostrar como texto
    return `<pre class="text-red-400 p-4 bg-red-900/20 rounded">Erro ao processar conteúdo: ${String(error)}</pre>`
  }
}

// ================================================
// Funções utilitárias (opcionais)
// ================================================

/**
 * Extrai todos os cabeçalhos do markdown para criar um índice
 */
export const extractHeaders = (content: string): Array<{level: number, text: string, id: string}> => {
  if (!content) return []
  
  const headers: Array<{level: number, text: string, id: string}> = []
  const lines = content.split('\n')
  
  lines.forEach(line => {
    const match = line.match(/^(#{1,6})\s+(.+)$/)
    if (match) {
      const level = match[1].length
      const text = match[2].trim()
      const id = text
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '')
      
      headers.push({ level, text, id })
    }
  })
  
  return headers
}

/**
 * Conta palavras no markdown (ignorando sintaxe)
 */
export const countWords = (content: string): number => {
  if (!content) return 0
  
  // Remover blocos de código, markdown e HTML
  const plainText = content
    .replace(/```[\s\S]*?```/g, '') // Remover blocos de código
    .replace(/`[^`]+`/g, '')         // Remover código inline
    .replace(/\[.+?\]\(.+?\)/g, '$1') // Links: [texto](url) → texto
    .replace(/!\[.+?\]\(.+?\)/g, '')  // Remover imagens
    .replace(/#{1,6}\s+/g, '')        // Remover marcadores de cabeçalho
    .replace(/[>*+\-]+\s+/g, '')      // Remover marcadores de lista
    .replace(/<[^>]*>/g, '')          // Remover tags HTML
  
  // Contar palavras
  return plainText.trim().split(/\s+/).filter(word => word.length > 0).length
}

/**
 * Estima o tempo de leitura em minutos
 */
export const readingTime = (content: string): number => {
  const wordsPerMinute = 200 // Média de leitura
  const wordCount = countWords(content)
  return Math.ceil(wordCount / wordsPerMinute)
}

// Exportar a instância configurada para uso direto (caso necessário)
export default md