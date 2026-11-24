<?php

/*
|--------------------------------------------------------------------------
| Endurecimento da API (auditoria de segurança de 24/11/2026)
|--------------------------------------------------------------------------
*/

return [

    'rate_limits' => [
        'public_forms_per_minute' => (int) env('RATE_LIMIT_PUBLIC_FORMS_PER_MINUTE', 10),
        'public_forms_per_hour' => (int) env('RATE_LIMIT_PUBLIC_FORMS_PER_HOUR', 60),
        'internal_per_minute' => (int) env('RATE_LIMIT_INTERNAL_PER_MINUTE', 120),
        'webhooks_per_minute' => (int) env('RATE_LIMIT_WEBHOOKS_PER_MINUTE', 300),
    ],

    // GET/PATCH /api/registrations/{id} são públicos (formulário em etapas).
    // Depois desta janela a partir da criação, o cadastro deixa de ser lido/alterado
    // pela rota pública (responde 404), limitando o estrago de um link vazado.
    'registration_edit_window_minutes' => (int) env('REGISTRATION_EDIT_WINDOW_MINUTES', 120),

    // IPs dos proxies reversos à frente da API (separados por vírgula) ou "*".
    // Vazio = nenhum proxy confiável (o IP visto é o da conexão direta).
    'trusted_proxies' => env('TRUSTED_PROXIES'),

    // CORS: ver config/cors.php (variável CORS_ALLOWED_ORIGINS).

];
