<?php

namespace App\Exceptions;

use Exception;

class LoadApiException extends Exception
{
    public function report()
    {
        //\Log::debug('User not found');
    }
    public function render($request, Exception $exception)
    {
            return array(
                'code' => config('loadapi.HTTP_CODE_400'),
                'message' => config('loadapi.http_code_msg')[config('loadapi.HTTP_CODE_400')]
            );
    }
}
