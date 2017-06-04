<?php

namespace NetAngels\Section;

use NetAngels\ApiException;
use NetAngels\Entity\Disk;
use NetAngels\Entity\SasDisk;
use NetAngels\Entity\SsdDisk;
use NetAngels\Entity\Tariff;
use NetAngels\Entity\Vds;
use NetAngels\ValueObject\RebootType;
use NetAngels\ValueObject\VmRequisites;
use NetAngels\ValueObject\VmImage;
use NetAngels\ValueObject\VmUser;

class VmApi extends AbstractApi
{
    const API_METHOD = 'vms';

    /**
     * Создаёт новую облачную VDS.
     *
     * @param Vds $vds
     * @param VmImage|null $image Образ ОС виртуальной машины (для создвания на основе образа)
     * @param Disk|SasDisk|SsdDisk $disk Параметры диска для новой VDS.
     * @param bool $copyDisk Нужно ли копировать исходный диск (для создания ВДС на основе диска)
     * @return VmRequisites
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws ApiException
     */
    public function create(Vds $vds, VmImage $image = null, Disk $disk, $copyDisk = false)
    {
        if ($vds->isCreated()) {
            throw new \LogicException('Cannot create currently created VDS.');
        }
        if (!$this->api->getTariffsApi()->isExists($vds->getTariff())) {
            throw new \UnexpectedValueException('This tariff does not exists.');
        }
        $params = [
            'type' => $vds->getTariff()->getName(),
        ];
        if (!is_null($image)) {
            // создание VM на основе образа
            if (!$this->api->getOsApi()->existsByImage($image)) {
                throw new \InvalidArgumentException('Invalid image for vm creating.');
            }
            $params['image'] = $image->getImageId();
            $params['arch'] = $image->getArchitecture();
            $params['storage_type'] = $disk->getType();
            $params['storage_size'] = (string)$disk->getSize();
        } else {
            // создание VM на основе существующего диска
            if (!$disk->isCreated()) {
                throw new \UnexpectedValueException('Cannot create VDS on non-created disk.');
            }
            $params['storage'] = $disk->getId();
            if ($copyDisk) {
                $params['storage_copy_origin'] = 'true';
            }
        }

        $result = $this->request->post(self::API_METHOD, $params);
        try {
            $users = $result->getData('users');
            foreach ($users as &$user) {
                $password = reset($user);
                $username = key($user);
                $user = new VmUser($username, $password);
                unset($user);
            }
        } catch (\Exception $e) {
            $users = [];
        } finally {
            return new VmRequisites($result->getData('ip'), $users);
        }
    }

    /**
     * Обновляет некоторые свойства VDS:
     *  name - название,
     *  tariff - тариф.
     * Вернёт новый объект @see Vds с изменениями.
     *
     * @param Vds $vds
     * @param array $data
     * @return Vds
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function update(Vds $vds, array $data)
    {
        $fields = [];
        if (isset($data['name']) && $data['name'] !== $vds->getName()) {
            if (empty($data['name'])) {
                throw new \InvalidArgumentException('VDS name cannot be empty.');
            }
            $fields['name'] = $data['name'];
        }
        if (isset($data['tariff'])) {
            if (!$data['tariff'] instanceof Tariff || !$this->api->getTariffsApi()->isExists($data['tariff'])) {
                throw new \InvalidArgumentException('Invalid tariff given.');
            }
            if ($data['tariff']->getName() !== $vds->getTariff()->getName()) {
                $fields['type'] = $data['tariff']->getName();
            }
        }
        if (empty($fields)) {
            throw new \UnexpectedValueException('There is nothing to update.');
        }
        return $this->buildVdsObject(
            $this->request->patch(self::API_METHOD, [$vds->getId()], $fields)->getData()
        );
    }

    /**
     * Запускает виртуальную машину, если она не работает.
     *
     * @param Vds $vds
     * @return Vds
     * @throws \RuntimeException Если VDS нельзя запустить
     */
    public function start(Vds $vds)
    {
        if ($vds->isActive()) {
            return $vds;
        }
        if (!$vds->isStopped()) {
            throw new \RuntimeException('This VDS cannot be started.');
        }
        return $this->buildVdsObject(
            $this->request->post(self::API_METHOD, [], [$vds->getId(), 'start'])->getData()
        );
    }

    /**
     * Останавливает работу VDS, если она работает.
     *
     * @param Vds $vds
     * @return Vds
     * @throws \RuntimeException Если VDS нельзя остановить
     */
    public function stop(Vds $vds)
    {
        if ($vds->isStopped()) {
            return $vds;
        }
        if (!$vds->isActive()) {
            throw new \RuntimeException('This VDS cannot be stopped.');
        }
        return $this->buildVdsObject(
            $this->request->post(self::API_METHOD, [], [$vds->getId(), 'stop'])->getData()
        );
    }

    /**
     * Перезагружает VDS.
     *
     * @param Vds $vds
     * @param RebootType $reboot Вид перезагрузки
     * @return Vds
     */
    public function reboot(Vds $vds, RebootType $reboot)
    {
        return $this->buildVdsObject(
            $this->request->post(self::API_METHOD, [$vds->getId(), 'reboot', (string)$reboot])->getData()
        );
    }

    /**
     * Удаляет облачную VDS,
     * и все связанные диски, если указана соответствующая опция.
     *
     * @param Vds $vds
     * @param bool $withStorage
     * @return void
     */
    public function delete(Vds $vds, $withStorage = false)
    {
        $params = [];
        if ($withStorage) {
            $params = ['delete_storages' => $withStorage];
        }
        $this->request->delete(self::API_METHOD, [$vds->getId()], $params);
    }

    /**
     * Вернёт список облачных VDS.
     *
     * @return Vds[]
     */
    public function getList()
    {
        $list = [];
        $currPage = 1;
        do {
            $result = $this->request->page(self::API_METHOD, $currPage++);
            $list = array_merge($list, $result->getData('results'));
        } while (null != $result->getData('next'));

        foreach ($list as &$ip) {
            $ip = $this->buildVdsObject($ip);
            unset($ip);
        }
        return $list;
    }

    /**
     * Вернёт информацию облачной VDS по её ID.
     *
     * @param int $id
     * @return Vds
     */
    public function getVds($id)
    {
        return $this->buildVdsObject($this->request->get(self::API_METHOD, [$id])->getData());
    }

    /**
     * Конструктор объекта @see Vds
     *
     * @param array $data
     * @return Vds
     */
    protected function buildVdsObject(array $data)
    {
        return new Vds(
            $this->api,
            new Tariff($data['type'], $data['memsize'], $data['ncpu']),
            $data['name'],
            $data['id'],
            $data['state'],
            $data['monitoring_enabled']
        );
    }
}
