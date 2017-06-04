<?php

namespace NetAngels\Entity;

/**
 * Тариф облачной VDS хостинга NetAngels.
 */
class Tariff
{
    private $name;
    private $memorySize;
    private $cpuCount;

    /**
     * @param string $name Название тарифа
     * @param int $memorySize Размер оперативной памяти в мегабайтах
     * @param int $cpuCount Количество ядер CPU
     */
    public function __construct($name, $memorySize, $cpuCount)
    {
        $this->name = $name;
        $this->memorySize = $memorySize;
        $this->cpuCount = $cpuCount;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getMemorySize()
    {
        return $this->memorySize;
    }

    /**
     * @return int
     */
    public function getCpuCount()
    {
        return $this->cpuCount;
    }
}
