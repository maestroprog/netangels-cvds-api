<?php

namespace NetAngels\Section;

use NetAngels\Entity\Tariff;

/**
 * Предоставляет доступ к справочному API о воможных тарифах.
 */
class TariffsApi extends AbstractApi
{
    private $cache;

    /**
     * Вернёт самый слабый тариф из возможных.
     *
     * @return Tariff
     */
    public function getLowTariff()
    {
        /**
         * @var $lowest Tariff|null
         */
        $lowest = null;
        $tariffs = $this->getList();
        foreach ($tariffs as $tariff) {
            if (
                is_null($lowest)
                || $lowest->getMemorySize() > $tariff->getMemorySize()
                || $lowest->getCpuCount() > $tariff->getCpuCount()
            ) {
                $lowest = $tariff;
            }
        }
        if (is_null($lowest)) {
            throw new \RuntimeException('Cannot retrieve low tariff.');
        }
        return $lowest;
    }

    /**
     * Вернёт самый мощный тариф из возможных.
     *
     * @return Tariff
     */
    public function getPowerfulTariff()
    {
        /**
         * @var $lowest Tariff|null
         */
        $lowest = null;
        $tariffs = $this->getList();
        foreach ($tariffs as $tariff) {
            if (
                is_null($lowest)
                || $lowest->getMemorySize() < $tariff->getMemorySize()
                || $lowest->getCpuCount() < $tariff->getCpuCount()
            ) {
                $lowest = $tariff;
            }
        }
        if (is_null($lowest)) {
            throw new \RuntimeException('Cannot retrieve low tariff.');
        }
        return $lowest;
    }


    /**
     * @return Tariff[]
     */
    public function getList()
    {
        if (!is_null($this->cache)) {
            return $this->cache;
        }
        $result = [];
        foreach ($this->request->get('vm-tariffs')->getData() as $tariff) {
            $result[] = new Tariff($tariff['name'], $tariff['memsize'], $tariff['ncpu']);
        }
        return $this->cache = $result;
    }

    /**
     * Проверяет существование тарифа на хостинге NetAngels.
     *
     * @param Tariff $checkingTariff
     * @return bool
     */
    public function isExists(Tariff $checkingTariff)
    {
        $tariffs = $this->getList();
        foreach ($tariffs as $tariff) {
            if ($tariff->getName() === $checkingTariff->getName()) {
                return true;
            }
        }
        return false;
    }
}
