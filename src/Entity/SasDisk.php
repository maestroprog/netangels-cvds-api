<?php

namespace NetAngels\Entity;

use NetAngels\Api;

/**
 * SAS диск "стандартной" скорости.
 */
class SasDisk extends Disk
{
    public function __construct(Api $api, $size, $name = null, $id = null, $state = null, $vdsId = null)
    {
        parent::__construct($api, 'vg', $size, $name = null, $id, $state, $vdsId);
    }
}
