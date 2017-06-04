<?php

namespace NetAngels\ValueObject;

class VmRequisites
{
    private $ip;
    private $users;

    /**
     * @param string $ip IP созданной облачной VDS
     * @param VmUser[] $users Пользователи новой VDS
     */
    public function __construct($ip, $users)
    {
        $this->ip = $ip;
        $this->users = $users;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Возвращает массив пользователей для созданной VDS.
     * Вернёт пустой массив, если VDS была создана на основе существующего диска.
     *
     * @return VmUser[]
     */
    public function getUsers()
    {
        return $this->users;
    }
}
