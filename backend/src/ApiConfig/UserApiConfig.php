<?php

namespace App\ApiConfig;

class UserApiConfig
{
    public const string GET = 'api:users:get';
    public const string GET_COLLECTION = 'api:users:get_collection';
    public const string PATCH = 'api:users:patch';
    public const string VALIDATE_PASSWORD_UPDATE = 'validation:password_update';
    public const array _VIEW = [
        self::GET,
        self::GET_COLLECTION,
    ];
}
