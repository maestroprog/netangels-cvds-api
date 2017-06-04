<?php

namespace NetAngels;

final class ApiToken
{
    private $token;
    private $lastUsedAt;

    /**
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
        $this->lastUsedAt = time();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->lastUsedAt > time() - 86400;
    }

    /**
     * Обновляет метку времени последнего использования токена.
     *
     * @return void
     */
    public function refresh()
    {
        $this->lastUsedAt = time();
    }

    public function __toString()
    {
        return $this->token;
    }
}
