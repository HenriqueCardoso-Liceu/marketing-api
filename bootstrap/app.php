<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Rotas da API sempre respondem em JSON, mesmo sem "Accept: application/json".
        // Sem isso, uma validação recusada vira um 302 para "/" e o cliente
        // (ex.: webhooks de outros sistemas) não fica sabendo qual campo falhou.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        // ValidationException não é reportada por padrão. Registra quais campos
        // falharam nas rotas da API para dar para investigar pelo laravel.log.
        // Só as mensagens de erro (nome do campo + regra), nunca os valores enviados.
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                Log::warning('API: validação recusada', [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'errors' => $e->errors(),
                ]);
            }

            return null; // segue para a renderização padrão (422 em JSON)
        });
    })->create();
