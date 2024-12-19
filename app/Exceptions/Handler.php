<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
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
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (NotFoundHttpException $e, $request) {
        if ($request->is('api/*')) {
            return response()->json([
                'code'=>404,
                'status'=>false,
                'message' => 'Record not found, Enter correct url.'
            ], 404);
        }
    });

        $this->renderable(function (RouteNotFoundException $e, $request) {
            return response()->json([
                'code'=>401,
                'status'=>false,
                'message' => 'Unauthorized access, Bearer Token is required.'
            ], 401);
    
    });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'code'=>405,
                'status'=>false,
                'message' => 'Requested method not allowed.'
            ], 405);
    
    });


    }
}
