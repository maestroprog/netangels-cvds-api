<?php

namespace NetAngels\Section;

use NetAngels\Api;
use NetAngels\ApiRequest;

abstract class AbstractApi
{
    protected $api;
    protected $request;

    public function __construct(Api $api)
    {
        $this->api = $api;
        $this->request = new ApiRequest($api->getToken());
    }
}
