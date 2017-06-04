<?php

namespace NetAngels;

use NetAngels\Section\DiskApi;
use NetAngels\Section\OsApi;
use NetAngels\Section\TariffsApi;
use NetAngels\Section\VmApi;
use NetAngels\Section\VmsIpApi;

/**
 * Класс, описывающий возможности NetAngels API.
 */
class Api
{
    private $token;

    public function __construct(ApiKey $key, ApiToken $token = null)
    {
        if (is_null($token)) {
            $token = $this->requestToken($key);
        }
        $this->token = $token;
    }

    /**
     * @return ApiToken
     */
    public function getToken()
    {
        return $this->token;
    }

    public function getVmApi()
    {
        return new VmApi($this);
    }

    public function getDiskApi()
    {
        return new DiskApi($this);
    }

    public function getVmsIpApi()
    {
        return new VmsIpApi($this);
    }

    public function getTariffsApi()
    {
        return new TariffsApi($this);
    }

    public function getOsApi()
    {
        return new OsApi($this);
    }

    /**
     * Запрашивает у NetAngels API новый токен.
     *
     * @param ApiKey $key
     * @return ApiToken
     * @throws \Exception
     */
    protected function requestToken(ApiKey $key)
    {
        try {
            $token = (new ApiRequest())
                ->post('token', ['api_key' => $key])
                ->getData('token');
        } catch (\Exception $e) {
            throw new \Exception('Cannot receive token.', $e->getCode(), $e);
        }
        return new ApiToken($token);
    }
}
