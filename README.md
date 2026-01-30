# AI Email Chatbot Automation (n8n)

An AI-powered email assistant built with n8n that automatically analyzes incoming emails, retrieves knowledge from engineering documents, and sends AI-generated replies.

## 🚀 Features
- Gmail Trigger for unread emails
- AI classification to determine if System Assurance (SA) response is required
- Retrieval-Augmented Generation (RAG) using Supabase Vector Store
- OpenAI & Gemini LLM integration
- Conversational memory for context handling
- Automatic AI email reply to sender
- Telegram trigger for multi-channel interaction

## 🧠 Architecture
Gmail → AI Classifier → Vector DB Retrieval → AI Agent → Email Reply

## 🛠 Tech Stack
- n8n (self-hosted)
- OpenAI GPT-4.1
- Google Gemini
- Supabase Vector Database
- Gmail API
- Telegram Bot API
- Docker

## 📦 How to Import
1. Open n8n dashboard
2. Go to Workflows → Import
3. Upload `workflows/email-ai-chatbot.json`
4. Configure credentials (Gmail, OpenAI, Supabase)

## ⚠️ Notes
- API keys are removed for security
- Use `.env.example` for environment variables

## 👤 Author
Setiawan  
AI Automation Engineer
