<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Validation Error',
                    'messages' => $exception->errors()
                ], 422);
            }
        }

        $status = 500;
        $error = 'Internal Server Error';
        $message = 'An unexpected error occurred.';

        if ($exception instanceof HttpException) {
            $status = $exception->getStatusCode();
            $message = $exception->getMessage() ?: 'HTTP Error';
            
            if ($status === 405) {
                $error = 'Method Not Allowed';
            } elseif ($status === 404) {
                $error = 'Not Found';
            } else {
                $error = 'HTTP Error';
            }
        } elseif ($exception instanceof ModelNotFoundException) {
            $status = 404;
            $error = 'Not Found';
            $message = 'Resource not found.';
        } elseif ($exception instanceof AuthorizationException) {
            $status = 401;
            $error = 'Unauthorized';
            $message = $exception->getMessage();
        } else {
            // Se for um erro 500 genérico, pegue a mensagem apenas em debug
            if (env('APP_DEBUG', false)) {
                $message = $exception->getMessage();
            }
        }

        $response = [
            'error' => $error,
            'message' => $message,
        ];

        if (env('APP_DEBUG', false)) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                // omitimos o trace inteiro para não explodir a resposta, mas pode ser adicionado
            ];
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($response, $status);
        }

        return parent::render($request, $exception);
    }
}
