<?php

namespace App\ApiConfig;

class ForgottenPasswordRequestApiConfig
{
    public const string GET = 'api:forgotten_password:get';
    public const string POST = 'api:forgotten_password:post';
    public const string VALIDATE_FORGOTTEN = 'validation:forgotten_password';
}
