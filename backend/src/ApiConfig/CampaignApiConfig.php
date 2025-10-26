<?php

namespace App\ApiConfig;

class CampaignApiConfig
{
    public const string GET = 'api:campaigns:get';
    public const string GET_COLLECTION = 'api:campaigns:get_collection';
    public const string POST = 'api:campaigns:post';
    public const string PATCH = 'api:campaigns:patch';

    public const array _VIEW = [
        self::GET,
        self::GET_COLLECTION,
    ];
}
