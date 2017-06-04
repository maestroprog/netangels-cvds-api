<?php

namespace NetAngels\Entity;

use NetAngels\Api;

/**
 * Высокопроизводительный SSD диск.
 */
class SsdDisk extends Disk
{
    public function __construct(Api $api, $size, $name = null, $id = null, $state = null, $vdsId = null)
    {
        parent::__construct($api, 'vgssd', $size, $name, $id, $state, $vdsId);
    }
}
