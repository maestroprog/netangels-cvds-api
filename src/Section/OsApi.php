<?php

namespace NetAngels\Section;

use NetAngels\Entity\OsImage;
use NetAngels\ValueObject\VmImage;

/**
 * Предоставляет доступ к справочному API о возможных образах ОС.
 */
class OsApi extends AbstractApi
{
    private $cache;

    /**
     * @return OsImage[]
     */
    public function getList()
    {
        if (!is_null($this->cache)) {
            return $this->cache;
        }
        $list = [];
        $currPage = 1;
        do {
            $result = $this->request->page('os-images', $currPage++);
            $list = array_merge($list, $result->getData('results'));
        } while (null != $result->getData('next'));

        foreach ($list as &$img) {
            // todo it is fix for NetAngels api bug
            $data = json_decode($a = str_replace(['u\'', '\''], ['"', '"'], $img['required_data']), true);
            $img = new OsImage($img['id'], $img['description'], $img['arch'], $data);
            unset($img);
        }
        return $this->cache = $list;
    }

    /**
     * @param VmImage $vmImage
     * @return bool
     */
    public function existsByImage(VmImage $vmImage)
    {
        $images = $this->getList();
        foreach ($images as $image) {
            if (
                $image->getId() === $vmImage->getImageId()
                && in_array($vmImage->getArchitecture(), $image->getArch())
            ) {
                return true;
            }
        }
        return false;
    }
}
