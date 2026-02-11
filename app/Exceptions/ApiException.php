<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiException extends HttpResponseException
{
    // Конструктор исключения
    public function __construct(string $message, int $code, $errors = [])
    {
        $exception = [
            'message' => $message,
            'code' => $code,
        ];

        if (!empty($errors)) {
            $exception['errors'] = $errors;
        }

        parent::__construct( response()->json($exception, $code) );
    }
}
