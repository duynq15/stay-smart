<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Chatbot LLM — FREE providers only
    | provider: gemini | groq | openrouter | ollama
    */
    'chatbot' => [
        'use_ai' => env('CHATBOT_USE_AI', false),
        'provider' => env('CHATBOT_PROVIDER', 'gemini'),
        'model' => env('CHATBOT_MODEL'),

        // Google Gemini — free tier ~15 RPM (https://aistudio.google.com)
        'gemini_key' => env('GEMINI_API_KEY'),

        // Groq Cloud — free, very fast LLama 3.x (https://console.groq.com)
        'groq_key' => env('GROQ_API_KEY'),

        // OpenRouter — gateway with free models (https://openrouter.ai)
        'openrouter_key' => env('OPENROUTER_API_KEY'),

        // Ollama (local self-host, no API key)
        'ollama_url' => env('OLLAMA_URL', 'http://localhost:11434'),

        'system_prompt' => env('CHATBOT_SYSTEM_PROMPT'),
    ],

];
