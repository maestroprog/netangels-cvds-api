<?php

namespace NetAngels\Entity;

/**
 * IP адрес облачной VDS.
 */
class VdsIp
{
    private $id;
    private $ip;
    private $vdsId;

    /**
     * @param int $id Id записи об IP адресе
     * @param string $ip IP адрес
     * @param int $vdsId Id облачной VDS использующей данный адрес.
     */
    public function __construct($id, $ip, $vdsId)
    {
        $this->id = $id;
        $this->ip = $ip;
        $this->vdsId = $vdsId;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Является ли данный IP адрес частью виртуальной приватной сети.
     *
     * @return bool
     */
    public function inPrivateNetwork()
    {
        return strpos($this->ip, '192.168.') === 0;
    }

    /**
     * @return int
     */
    public function getVdsId()
    {
        return $this->vdsId;
    }
}
