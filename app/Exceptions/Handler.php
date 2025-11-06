<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log all exceptions with context
            $this->logException($e);
        });

        // Custom rendering for specific exceptions
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Resource not found.',
                    'error' => 'Not Found'
                ], 404);
            }
            return response()->view('errors.404', [], 404);
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Access denied.',
                    'error' => 'Forbidden'
                ], 403);
            }
            return response()->view('errors.403', [], 403);
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Resource not found.',
                    'error' => 'Not Found'
                ], 404);
            }
            return response()->view('errors.404', [], 404);
        });
    }

    /**
     * Log exception with additional context
     */
    protected function logException(Throwable $exception): void
    {
        $user = Auth::check() ? Auth::user() : null;

        $context = [
            'user_id' => $user ? $user->id : 'guest',
            'user_email' => $user ? $user->email : 'guest',
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];

        // Log to different channels based on exception type
        if (
            $exception instanceof AuthenticationException ||
            $exception instanceof AuthorizationException
        ) {
            Log::channel('security')->warning('Security Exception', array_merge($context, [
                'message' => $exception->getMessage(),
            ]));
        } elseif (
            $exception instanceof \PDOException ||
            $exception instanceof \Illuminate\Database\QueryException
        ) {
            Log::channel('database')->error('Database Exception', array_merge($context, [
                'message' => $exception->getMessage(),
            ]));
        } else {
            Log::error('Application Exception', array_merge($context, [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]));
        }
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Unauthorized'
            ], 401);
        }

        return redirect()->guest(route('login'));
    }
}