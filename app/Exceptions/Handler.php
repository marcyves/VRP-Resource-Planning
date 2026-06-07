<?php

namespace App\Exceptions;

use App\Support\DatabaseAvailability;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($request instanceof Request && app(DatabaseAvailability::class)->isConnectionError($e)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('messages.maintenance'),
                ], 503);
            }

            return response()->view('maintenance', [], 503);
        }

        return parent::render($request, $e);
    }
}
