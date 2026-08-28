# AI Email Assistant (n8n)

An AI-powered email assistant built with n8n that automatically classifies incoming emails, retrieves relevant knowledge from technical/contract reference documents via RAG, and sends AI-generated replies — with a secondary chat/Telegram interface for the same knowledge base.

## Features

- **Gmail trigger** for unread emails
- **AI classification** — determines sender category and whether a response is required, based on configurable rules
- **Retrieval-Augmented Generation (RAG)** using a Supabase vector store over reference documents (technical specifications, correspondence guidelines, etc.)
- **OpenAI & Google Gemini** LLM integration (with fallback)
- **Conversational memory** for multi-turn context handling
- **Automatic AI email reply** to the original sender
- **Telegram trigger** for multi-channel access to the same knowledge base

## Architecture

```
Gmail Trigger -> AI Classifier -> (if response needed) -> Vector DB Retrieval -> AI Agent -> Email Reply
Telegram Trigger -----------------------------------------------------------------------------^
```

## Tech Stack

- n8n (self-hosted)
- OpenAI GPT-4.1-mini
- Google Gemini (fallback LLM)
- Supabase (vector database)
- Gmail API
- Telegram Bot API
- Docker

## How to Import

1. Open your n8n dashboard
2. Go to **Workflows → Import**
3. Upload the `workflows` file from this repo (n8n workflow export JSON)
4. Configure your own credentials (Gmail OAuth2, OpenAI, Supabase, Telegram)
5. Adjust the classification rules and vector store table name to your own use case

## Notes

- All credentials and instance-specific IDs are stripped from the exported workflow — you must configure your own
- Sender classification categories, document references, and recipient addresses in this export are generic placeholders — replace with your own project's stakeholder categories and knowledge base

## Author

Setiawan — AI Automation Engineer (n8n)

---
*Built to automate correspondence triage and technical Q&A for an infrastructure project. Project-identifying details, real stakeholder names, and contract document references have been removed from this public copy.*
