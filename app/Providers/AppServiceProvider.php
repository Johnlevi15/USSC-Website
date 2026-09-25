<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        RateLimiter::for('admin-login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email', ''));

            return Limit::perMinute(5)
                ->by($email.'|'.$request->ip())
                ->response(fn (Request $request, array $headers): Response => $this->throttledResponse($request, $headers, 'admin-login'));
        });

        RateLimiter::for('admin-password-reset', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email', ''));

            return Limit::perMinute(5)
                ->by($email.'|'.$request->ip())
                ->response(fn (Request $request, array $headers): Response => $this->throttledResponse($request, $headers, 'admin-password-reset'));
        });

        RateLimiter::for('uploads', function (Request $request): Limit {
            return Limit::perMinutes(10, 10)
                ->by($request->ip())
                ->response(fn (Request $request, array $headers): Response => $this->throttledResponse($request, $headers, 'uploads'));
        });

        RateLimiter::for('chatbot', function (Request $request): Limit {
            return Limit::perMinute(30)
                ->by($request->ip())
                ->response(fn (Request $request, array $headers): Response => $this->throttledResponse($request, $headers, 'chatbot'));
        });
    }

    private function throttledResponse(Request $request, array $headers, string $limiter): Response
    {
        Log::channel('security')->warning('Rate limit exceeded.', [
            'limiter' => $limiter,
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response('Too Many Attempts.', 429, $headers);
    }
}
