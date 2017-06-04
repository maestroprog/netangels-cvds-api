<?php

namespace NetAngels\ValueObject;

/**
 * Объект-значения образа для создания виртуальной машины.
 */
class VmImage
{
    private $imageId;
    private $architecture;

    /**
     * Инстанцирует объект образа на основе пользовательских данных.
     *
     * @param $imageId
     * @param $architecture
     * @return self
     */
    public static function custom($imageId, $architecture)
    {
        return new self($imageId, $architecture);
    }

    /**
     * VmImage constructor.
     * @param int $imageId
     * @param string $architecture Архитектура виртуальной машины
     */
    private function __construct($imageId, $architecture)
    {
        $this->imageId = $imageId;
        $this->architecture = $architecture;
    }

    /**
     * @return int
     */
    public function getImageId()
    {
        return $this->imageId;
    }

    /**
     * @return string
     */
    public function getArchitecture()
    {
        return $this->architecture;
    }
}
