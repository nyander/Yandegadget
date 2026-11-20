<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Payment providers post webhooks without a CSRF token; they're verified by signature instead.
        $middleware->preventRequestForgery(except: ['webhooks/*']);

        $middleware->append(SecurityHeaders::class);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Friendly error pages for the shop and staff app, outside debug mode.
        // The admin panel and JSON requests keep Laravel's own handling.
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $status = $response->getStatusCode();

            if (config('app.debug') || $request->expectsJson() || $request->is('admin', 'admin/*')
                || ! in_array($status, [403, 404, 419, 429, 500, 503], true)) {
                return $response;
            }

            if ($status === 419) {
                Inertia::flash('toast', ['type' => 'error', 'message' => 'Your session expired. Please try again.']);

                return back();
            }

            // Unmatched URLs skip the web middleware, so share the layout's props here.
            Inertia::share(app(HandleInertiaRequests::class)->share($request));

            return Inertia::render('error', ['status' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
