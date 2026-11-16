<?php

use App\Http\Middleware\SetBranchScope;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Branch data isolation (BRD module #21): binds the selected branch
        // for authenticated institute users so the "branch" global scope on
        // Student/Transaction filters every query to that branch only.
        // Appended to the web group so it runs after StartSession/auth.
        $middleware->web(append: SetBranchScope::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
