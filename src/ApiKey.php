<?php

namespace NetAngels;

/**
 * Класс API ключа к API NetAngels.
 */
final class ApiKey
{
    private $key;

    /**
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    public function __toString()
    {
        return $this->key;
    }
}
