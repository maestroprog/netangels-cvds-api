<?php

namespace NetAngels\Section;

use NetAngels\ApiException;
use NetAngels\Entity\Vds;
use NetAngels\Entity\VdsIp;

/**
 * Доступ к информации об использумых IP адресах облачными VDS.
 */
class VmsIpApi extends AbstractApi
{
    /**
     * Список всех использующихся IP адресов.
     *
     * @return VdsIp[]
     */
    public function getList()
    {
        $list = [];
        $currPage = 1;
        do {
            $result = $this->request->page('ips', $currPage++);
            $list = array_merge($list, $result->getData('results'));
        } while (null != $result->getData('next'));

        foreach ($list as &$ip) {
            $ip = $this->buildVdsIpObject($ip);
            unset($ip);
        }
        return $list;
    }

    /**
     * Информация об одном из используемых IP адресов.
     *
     * @param int $id
     * @return VdsIp
     * @throws ApiException
     */
    public function getListById($id)
    {
        $result = $this->request->get('ips', [$id])->getData();
        return $this->buildVdsIpObject($result);
    }

    /**
     * Информация об используемых IP адресах одной виртуальной машины.
     *
     * @param Vds $vds Объект виртуальной машины
     * @return VdsIp[]
     * @throws ApiException
     */
    public function getListByVm(Vds $vds)
    {
        return $this->getListByVdsId($vds->getId());
    }

    /**
     * Информация об используемых IP адресах одной виртуальной машины.
     *
     * @param int $vdsId ID виртуальной машины
     * @return VdsIp[]
     */
    public function getListByVdsId($vdsId)
    {
        $list = [];
        $currPage = 1;
        do {
            $result = $this->request->page('ips', $currPage++, ['vm' => $vdsId]);
            $list = array_merge($list, $result->getData('results'));
        } while (null != $result->getData('next'));

        foreach ($list as &$ip) {
            $ip = $this->buildVdsIpObject($ip);
            unset($ip);
        }
        return $list;
    }

    /**
     * Общий конструктор объектов @see VdsIp.
     *
     * @param array $ipData
     * @return VdsIp
     * @throws ApiException
     */
    protected function buildVdsIpObject(array $ipData)
    {
        if (!isset($ipData['id']) || !isset($ipData['ip']) || !isset($ipData['vm'])) {
            throw new ApiException('Unknown API response.');
        }
        return new VdsIp($ipData['id'], $ipData['ipvalue'], $ipData['vm']);
    }
}
