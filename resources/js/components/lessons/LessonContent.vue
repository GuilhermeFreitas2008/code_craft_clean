<!-- components/lessons/LessonContent.vue -->
<template>
  <div class="prose prose-invert max-w-none" v-html="renderedContent"></div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import MarkdownIt from 'markdown-it'
import hljs from 'highlight.js'

const props = defineProps<{
  content: string
}>()

// Configurar markdown-it
const md: MarkdownIt = new MarkdownIt({
  html: true,
  linkify: true,
  typographer: true,
  highlight: function (str: string, lang: string): string {
    if (lang && hljs.getLanguage(lang)) {
      try {
        const highlighted = hljs.highlight(str, { language: lang }).value
        return `<pre class="code-block"><code class="hljs language-${lang}">${highlighted}</code></pre>`
      } catch (__) {}
    }
    return `<pre class="code-block"><code>${md.utils.escapeHtml(str)}</code></pre>`
  }
})

const renderedContent = computed<string>(() => {
  return md.render(props.content)
})
</script>

<style>
/* Estilos base da prose */
.prose {
  color: #f8fafc;
  max-width: 65ch;
  font-size: 1rem;
  line-height: 1.75;
}

.prose :where(h1, h2, h3, h4) {
  color: #f8fafc;
  font-weight: 600;
  line-height: 1.25;
  margin-top: 2em;
  margin-bottom: 1em;
}

.prose h1 {
  font-size: 2.25em;
}

.prose h2 {
  font-size: 1.5em;
}

.prose h3 {
  font-size: 1.25em;
}

.prose p {
  margin-top: 1.25em;
  margin-bottom: 1.25em;
  color: rgba(248, 250, 252, 0.8);
}

.prose strong {
  color: #f8fafc;
  font-weight: 600;
}

.prose code:not(pre code) {
  color: #3b82f6;
  font-size: 0.875em;
  font-family: ui-monospace, monospace;
  background: rgba(59, 130, 246, 0.1);
  padding: 0.2em 0.4em;
  border-radius: 0.25rem;
}

/* Blocos de código melhorados */
.prose .code-block {
  background: #0f172a;
  border: 1px solid rgba(59, 130, 246, 0.2);
  border-radius: 0.75rem;
  padding: 1.25rem;
  overflow-x: auto;
  margin: 1.5em 0;
  position: relative;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.prose .code-block::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  border-radius: 0.75rem 0.75rem 0 0;
}

.prose .code-block code {
  color: #e2e8f0;
  font-size: 0.875em;
  font-family: 'Fira Code', 'JetBrains Mono', ui-monospace, monospace;
  line-height: 1.6;
  background: transparent;
  padding: 0;
}

/* Cores do highlight.js - tema personalizado */
.prose .hljs-keyword,
.prose .hljs-selector-tag,
.prose .hljs-built_in {
  color: #f472b6;
}

.prose .hljs-title,
.prose .hljs-section,
.prose .hljs-function {
  color: #93c5fd;
}

.prose .hljs-string,
.prose .hljs-regexp,
.prose .hljs-number {
  color: #6ee7b7;
}

.prose .hljs-comment,
.prose .hljs-quote {
  color: #94a3b8;
  font-style: italic;
}

.prose .hljs-variable,
.prose .hljs-template-variable {
  color: #fbbf24;
}

.prose .hljs-operator,
.prose .hljs-punctuation {
  color: #cbd5e1;
}

.prose .hljs-tag,
.prose .hljs-name {
  color: #f87171;
}

.prose .hljs-attr {
  color: #93c5fd;
}

.prose .hljs-attribute {
  color: #6ee7b7;
}

/* Listas */
.prose :where(ul, ol) {
  padding-left: 1.5em;
  margin: 1.25em 0;
}

.prose ul {
  list-style-type: disc;
}

.prose ol {
  list-style-type: decimal;
}

.prose li {
  margin-bottom: 0.25em;
  color: rgba(248, 250, 252, 0.8);
}

.prose li::marker {
  color: #3b82f6;
}

/* Links */
.prose a {
  color: #3b82f6;
  text-decoration: none;
  border-bottom: 1px solid transparent;
  transition: border-color 0.2s;
}

.prose a:hover {
  border-bottom-color: #3b82f6;
}

/* Blockquotes */
.prose blockquote {
  border-left-width: 4px;
  border-left-color: rgba(59, 130, 246, 0.3);
  padding-left: 1em;
  font-style: italic;
  margin: 1.5em 0;
  background: rgba(59, 130, 246, 0.05);
  padding: 1em 1.5em;
  border-radius: 0.5rem;
}

.prose blockquote p {
  margin: 0;
  color: rgba(248, 250, 252, 0.9);
}

/* Tabelas (se aparecerem) */
.prose table {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5em 0;
}

.prose th {
  background: rgba(59, 130, 246, 0.1);
  color: #f8fafc;
  font-weight: 600;
  padding: 0.75em;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.prose td {
  padding: 0.75em;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(248, 250, 252, 0.8);
}

.prose tr:nth-child(even) {
  background: rgba(255, 255, 255, 0.02);
}
</style>