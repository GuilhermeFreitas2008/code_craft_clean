# Code Craft 🚀

O **Code Craft** é uma plataforma moderna de e-learning focada no ensino de programação. Este ecossistema combina uma interface de utilizador limpa, minimalista e reativa para os alunos com um painel de administração robusto para a gestão de conteúdos.

---

## 🛠️ Tecnologias Utilizadas

A aplicação foi desenvolvida dividindo as responsabilidades entre o ecossistema do servidor (backend) e a interface do utilizador (frontend):

* **Backend:** Laravel (v10/v11) & Filament PHP (Painel Administrativo)
* **Frontend:** Vue.js 3, TypeScript & Vite
* **Estilização:** Tailwind CSS & Lucide Vue (Ícones)
* **Gestão de Estado:** Pinia
* **Base de Dados & Storage:** Supabase (PostgreSQL & Buckets de Armazenamento)

---

## ✨ Funcionalidades Principais

* **Área do Aluno (SPA):** Rápida e sem recarregamentos de página, graças à arquitetura com Vue.js.
* **Catálogo de Cursos Dinâmico:** Filtros por categoria e dificuldade, além de acompanhamento do estado do curso (Começar / Continuar).
* **Ambiente de Aula Avançado:** Suporte para vídeo-aulas e artigos técnicos em formato Markdown com realce de sintaxe (*Syntax Highlighting*).
* **Gestão Otimizada de Ficheiros:** Upload automático de materiais de apoio diretamente para os *Buckets* do Supabase, mantendo a base de dados leve.
* **Comunidade Integrada:** Redirecionamento dinâmico para o servidor oficial do Discord para utilizadores autenticados.

---

## 🚀 Como Executar o Projeto Localmente

Para correres este projeto no teu computador, segue os passos abaixo.

### Pré-requisitos
Garante que tens instalado na tua máquina:
* PHP (>= 8.1)
* Composer
* Node.js & npm

### 1. Clonar o Repositório
```bash
git clone [https://github.com/o-teu-utilizador/code-craft.git](https://github.com/o-teu-utilizador/code-craft.git)
cd code-craft
```
### 2. Configurar o Backend (Laravel)
Instala as dependências do PHP:
```bash
composer install
```
Cria o teu ficheiro de configuração local a partir do exemplo:
```bash
cp .env.example .env
```
**Nota**: Abre o ficheiro .env e coloca as tuas credenciais da base de dados e do Supabase.

Gera a chave de segurança da aplicação:
```bash
php artisan key:generate
```
Cria as tabelas na base de dados:
```bash
php artisan migrate
```
Cria a ligação para os ficheiros públicos:
```bash
php artisan storage:link
```
### 3. Configurar o Frontend (Vue.js)
Instala as dependências do JavaScript:
```bash
npm install
```
### 4. Iniciar os Servidores
```bash
php artisan serve
```
Noutro terminal, inicia o compilador do Vite para o frontend:
```bash
npm run dev
```
Agora, basta abrir o navegador no endereço indicado (geralmente http://127.0.0.1:8000).

## 🎯 Prova de Aptidão Profissional (PAP)
Este projeto foi desenvolvido como trabalho final para o Curso Técnico de Gestão e Programação de Sistemas Informáticos.
