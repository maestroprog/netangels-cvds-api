<?php

namespace NetAngels;

/**
 * Исключительная ситуация, возникающая в ходе работы с API.
 */
final class ApiException extends \Exception
{
    /**
     * ApiException constructor.
     * @param string $message Сообщение об ошибке от NetAngels Api
     * @param int $code HTTP код ответа
     */
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }
}
