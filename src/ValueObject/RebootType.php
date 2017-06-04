<?php

namespace NetAngels\ValueObject;

class RebootType
{
    const REBOOT = 0;
    const RESET = 1;

    const CODES = [
        self::REBOOT => 'soft',
        self::RESET => 'reset',
    ];

    private $type;

    /**
     * Маягкая (программная) перезагрузка.
     *
     * @return RebootType
     */
    public static function soft()
    {
        return new self(self::REBOOT);
    }

    /**
     * Жесткая перезагрузка (аппаратный сброс).
     *
     * @return RebootType
     */
    public static function hard()
    {
        return new self(self::RESET);
    }

    /**
     * @param int $type
     */
    private function __construct($type)
    {
        if (!in_array($type, [self::REBOOT, self::RESET])) {
            throw new \UnexpectedValueException('Unexpected reboot type: ' . $type);
        }
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::CODES[$this->type];
    }
}
