<?php

namespace NetAngels\Entity;

use NetAngels\Api;
use NetAngels\ValueObject\RebootType;
use NetAngels\ValueObject\VmImage;
use NetAngels\ValueObject\VmRequisites;

/**
 * Сущность облачной VDS.
 * Для получения информации о используемых дисках и IP адресах используется ленивая загрузка.
 * После получения данной информации она кешируется.
 */
class Vds
{
    const ALLOWED_STATES = [
        'pending', //  - в ожидании
        'building', // - в процессе создания
        'active', // - ресурс активен
        'stopped', // - остановлен
        'blocked', // - ресурс заблокирован
        'error' // - ошибка
    ];

    private $api;
    private $id;
    private $name;
    private $tariff;
    private $state;
    private $storage;
    private $ips;

    /**
     * @param Api $api
     * @param Tariff $tariff
     * @param string $name
     * @param int|null $id
     * @param string|null $state
     * @param bool|null $monitoringEnabled
     */
    public function __construct(
        Api $api,
        Tariff $tariff,
        $name = null,
        $id = null,
        $state = null,
        $monitoringEnabled = null
    )
    {
        if (isset($state) && !in_array($state, self::ALLOWED_STATES)) {
            throw new \InvalidArgumentException('Invalid vds state: ' . $state);
        }

        $this->api = $api;
        $this->id = $id;
        $this->name = $name;
        $this->tariff = $tariff;
        $this->state = $state;
    }

    /**
     * Создаёт облачную VDS с указанным образом ОС.
     *
     * @param VmImage $image Образ операционной системы для новой VDS
     * @param Disk $disk Параметры диска для новой облачной VDS
     * @return VmRequisites
     */
    public function createByImage($image, Disk $disk)
    {
        return $this->api->getVmApi()->create($this, $image, $disk);
    }

    /**
     * Создаёт данную виртуальную машину с использованием
     *
     * @param Disk $disk Параметры диска для новой облачной VDS
     * @param bool $copyDisk Нужно ли копировать исходный диск
     * @return VmRequisites
     * @throws \LogicException
     */
    public function createByExistStorage(Disk $disk, $copyDisk = false)
    {
        return $this->api->getVmApi()->create($this, null, $disk, $copyDisk);
    }

    /**
     * Переименовывает облачную VDS
     * и возвращает новый объект @see Vds с изменённым названием.
     *
     * @param $newName
     * @return Vds
     */
    public function rename($newName)
    {
        return $this->api->getVmApi()->update($this, ['name' => $newName]);
    }

    /**
     * Меняет тариф облачной VDS
     * и возвращает новый объект @see Vds с изменениями.
     *
     * @param Tariff $newTariff
     * @return Vds
     */
    public function changeTariff(Tariff $newTariff)
    {
        return $this->api->getVmApi()->update($this, ['tariff' => $newTariff]);
    }

    /**
     * Запускает VDS.
     *
     * @return Vds
     * @throws \RuntimeException Если VDS нельзя запустить.
     */
    public function start()
    {
        return $this->api->getVmApi()->start($this);
    }

    /**
     * Останавливает работу VDS.
     *
     * @return Vds
     * @throws \RuntimeException Если VDS нельзя остановить.
     */
    public function stop()
    {
        return $this->api->getVmApi()->stop($this);
    }

    /**
     * Выполняет мягкую перезагрузку.
     * Если VDS зависла, то это может не сработать.
     *
     * @return Vds
     */
    public function softReboot()
    {
        return $this->api->getVmApi()->reboot($this, RebootType::soft());
    }

    /**
     * Выполняет жёсткую перезагрузку.
     * Если VDS зависла, то это сработает,
     * однако дальнейшая работопригодность такой VDS под вопросом...
     * т.к. она может зависнуть снова, или вовсе не загрузиться.
     *
     * @return Vds
     */
    public function hardReboot()
    {
        return $this->api->getVmApi()->reboot($this, RebootType::hard());
    }

    /**
     * Удаляет облачную VDS,
     * и все связанные диски, если указана соответствующая опция.
     *
     * @param bool $withStorage Удалять связанные диски
     * @return void
     */
    public function delete($withStorage = false)
    {
        $this->api->getVmApi()->delete($this, $withStorage);
    }

    /**
     * Является ли данная машина уже созданной.
     *
     * @return bool
     */
    public function isCreated()
    {
        return !is_null($this->id);
    }

    /**
     * Работает ли VDS.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->state === 'active';
    }

    /**
     * Остановлена ли VDS.
     *
     * @return bool
     */
    public function isStopped()
    {
        return $this->state === 'stopped';
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Название виртуальной машины.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Вернёт код состояния виртуальной машины, или null если она ещё не создана.
     *
     * @return null|string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Вернёт тариф виртуальной машины.
     *
     * @return Tariff
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * Вернёт список дисков, примонтированных к данной виртуальной машине.
     *
     * @return SasDisk[]|SsdDisk[]
     */
    public function getStorage()
    {
        return $this->storage ?: $this->storage = $this->api->getDiskApi()->getListByVm($this);
    }

    /**
     * Вернёт список IP адресов, использующихся данной виртуальной машиной.
     *
     * @return VdsIp[]
     */
    public function getIps()
    {
        return $this->ips ?: $this->ips = $this->api->getVmsIpApi()->getListByVm($this);
    }
}
