<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
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
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenMismatchException){
            return redirect($request->fullUrl());
        }
        elseif($exception instanceOf MethodNotAllowedHttpException) {
            abort(404);
        }
        else if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            return redirect()->back()->withInput($request->except('password'))->with(['message' => 'Validation Token was expired. Please try again','alert-class' => 'alert-danger']);
        }
        else {
         /*   if(request()->wantsJson()) {
                return response()->json([
                    'status' => 'failed','error_message'   =>  __('messages.api.something_went_wrong_try_later'),
                ]);
            }
            // Handle all 500 exceptions
            if(method_exists('getStatusCode', $exception) && $exception->getStatusCode() == 500 && url()->previous() && url()->previous() != url()->current()) {
                return redirect()->back()->with(['message' => __('messages.api.something_went_wrong_try_later'),'alert-class' => 'alert-danger']);
            }*/
        }

        return parent::render($request, $exception);
    }
}