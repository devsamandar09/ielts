<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'IELTS Platform',
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,

    // AI API Keys
    'openaiApiKey' => env('CLAUDE_API_KEY', ''),
    'elevenLabsApiKey' => env('ELEVENLABS_API_KEY', ''),
];

