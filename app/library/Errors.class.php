<?php

class Errors
{
    
    const TOKEN_INVALID = -1000;
    const TOKEN_ILLEGAL = -1001;
    const TOKEN_EXPIRED = -1002;
    const TOKEN_CREATE_ERROR = -1003;
    const TOKEN_IMAGE_NO_TYPE = -1004;

    static public function getErrorMessage($code)
    {
        $errorMessages = new \Phalcon\Config\Adapter\Json(__DIR__ . '/../../app/config/errors.json');
        if (isset($errorMessages[$code])) {
            return $errorMessages[$code];
        }
        return 'unknown';
    }
}
