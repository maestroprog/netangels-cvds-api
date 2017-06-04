<?php

namespace NetAngels\Entity;

use NetAngels\Api;

abstract class Disk
{
    const ALLOWED_STATES = [
        'pending', //  - в ожидании
        'building', // - в процессе создания
        'active', // - ресурс активен
        'stopped', // - остановлен
        'blocked', // - ресурс заблокирован
        'error' // - ошибка
    ];

    const ALLOWED_TYPES = ['vg', 'vgssd'];

    protected $id;
    protected $name;
    protected $state;
    protected $type;
    protected $size;
    protected $vdsId;
    private $api;

    /**
     * Disk constructor.
     * @param Api $api
     * @param string $type
     * @param int $size
     * @param string $name
     * @param int|null $id
     * @param string|null $state
     * @param int|null $vdsId
     */
    public function __construct(Api $api, $type, $size, $name = null, $id = null, $state = null, $vdsId = null)
    {
        if (!in_array($type, self::ALLOWED_TYPES)) {
            throw new \InvalidArgumentException('Invalid disk type: ' . $type, '; allowed types: vg,vgssd.');
        }
        if (!is_int($size)) {
            throw new \InvalidArgumentException('Size value must be integer.');
        }
        $size = intval(ceil($size / 5) * 5);
        if ($size < 5 || $size > 250) {
            throw new \InvalidArgumentException('The size should be more than 5 and less than 250.');
        }
        if (isset($state) && !in_array($state, self::ALLOWED_STATES)) {
            throw new \InvalidArgumentException('Invalid disk state: ' . $state);
        }
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->id = $id;
        $this->state = $state;
        $this->vdsId = $vdsId;
        $this->api = $api;
    }

    /**
     * Изменяет размер облачной VDS
     * и возвращает новый объект с изменённым размером.
     * Нельзя изменить размер диска на тот же самый.
     *
     * @param $size
     * @return Disk
     */
    public function resize($size)
    {
        return $this->api->getDiskApi()->update($this, ['size' => $size]);
    }

    /**
     * Изменяет тип диска
     * и возвращает новый объект с изменениями.
     * Нельзя изменить тип диска на тот же самый.
     *
     * @param string $diskClass Класс диска, например @see SasDisk.
     * @return SasDisk|SsdDisk
     */
    public function changeType($diskClass)
    {
        switch ($diskClass) {
            case SasDisk::class:
                $type = 'vg';
                break;
            case SsdDisk::class:
                $type = 'vgssd';
                break;
            default:
                $type = $diskClass;
        }
        return $this->api->getDiskApi()->update($this, ['type' => $type]);
    }

    /**
     * Вернёт true, если диск уже создан.
     *
     * @return bool
     */
    public function isCreated()
    {
        return !is_null($this->id);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return int|null
     */
    public function getVdsId()
    {
        return $this->vdsId;
    }
}
