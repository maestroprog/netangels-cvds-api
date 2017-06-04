<?php

namespace NetAngels\Section;

use NetAngels\Entity\Disk;
use NetAngels\Entity\SasDisk;
use NetAngels\Entity\SsdDisk;
use NetAngels\Entity\Vds;

/**
 * Дисковое API.
 */
class DiskApi extends AbstractApi
{
    const API_METHOD = 'storages';

    /**
     * Обновляет некоторые параметры диска:
     *  type - тип диска (vg,vgssd)
     *  size - размер диска в ГБ
     *
     * @param Disk $disk
     * @param array $data
     * @return SasDisk|SsdDisk
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function update(Disk $disk, array $data)
    {
        $fields = [];
        if (isset($data['type'])) {
            if (!in_array($data['type'], Disk::ALLOWED_TYPES)) {
                throw new \InvalidArgumentException('Invalid disk type: ' . $data['type']);
            }
            if ($data['type'] !== $disk->getType()) {
                $fields['type'] = $data['type'];
            }
        }
        if (isset($data['size'])) {
            $size = intval(ceil($data['size'] / 5) * 5);
            if ($size < 5 || $size > 250) {
                throw new \InvalidArgumentException('The size should be more than 5 and less than 250.');
            }
            if ($size !== $disk->getSize()) {
                $fields['size'] = $size;
            }
        }
        if (empty($fields)) {
            throw new \UnexpectedValueException('There is nothing to update.');
        }
        return $this->buildDiskObject(
            $this->request->patch(self::API_METHOD, [$disk->getId()], $fields)
        );
    }

    /**
     * Получает список всех дисков.
     *
     * @return SasDisk[]|SsdDisk[]
     */
    public function getList()
    {
        $list = [];
        $currPage = 1;
        do {
            $result = $this->request->page(self::API_METHOD, $currPage++);
            $list = array_merge($list, $result->getData('results'));
        } while (null != $result->getData('next'));

        foreach ($list as &$disk) {
            $disk = $this->buildDiskObject($disk);
            unset($disk);
        }
        return $list;
    }

    /**
     * Получает информацию по ID диска.
     *
     * @param $id
     * @return SasDisk|SsdDisk
     */
    public function getDisk($id)
    {
        return $this->buildDiskObject($this->request->get(self::API_METHOD, [$id])->getData());
    }

    /**
     * @param Vds $vds
     * @return SasDisk[]|SsdDisk[]
     */
    public function getListByVm(Vds $vds)
    {
        return $this->getListByVmId($vds->getId());
    }

    /**
     * @param $vdsId
     * @return array
     */
    public function getListByVmId($vdsId)
    {
        $list = [];
        $currPage = 1;
        do {
            $result = $this->request->page(self::API_METHOD, $currPage++, ['vm' => $vdsId]);
            $list = array_merge($list, $result->getData('results'));
        } while (null != $result->getData('next'));

        foreach ($list as &$disk) {
            $disk = $this->buildDiskObject($disk);
            unset($disk);
        }
        return $list;
    }

    /**
     * @param $data
     * @return SasDisk|SsdDisk
     */
    protected function buildDiskObject($data)
    {
        switch ($data['type']) {
            case 'vg':
                return new SasDisk($this->api, $data['size'], $data['name'], $data['id'], $data['state'], $data['vm']);
            // no break
            case 'vgssd':
                return new SsdDisk($this->api, $data['size'], $data['name'], $data['id'], $data['state'], $data['vm']);
            // no break
            default:
                throw new \InvalidArgumentException('Invalid disk type: ' . $data['type']);
        }
    }
}
