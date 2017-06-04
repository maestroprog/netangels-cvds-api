<?php

namespace NetAngels;

final class ApiResult
{
    private $code;
    private $data;

    /**
     * @param int $code HTTP response code
     * @param string $json json-encoded body
     * @throws \InvalidArgumentException
     */
    public function __construct($code, $json)
    {
        $this->code = $code;
        if ($code != 204 && null === ($this->data = json_decode($json, true, 20))) {
            throw new \InvalidArgumentException('The response is not json-encoded data.');
        }
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $path
     * @return mixed
     * @throws \Exception
     */
    public function getData($path = null)
    {
        if (is_null($path)) {
            return $this->data;
        }
        $pathArray = explode('/', $path);
        $data = $this->data;
        foreach ($pathArray as $item) {
            if (!array_key_exists($item, $data)) {
                throw new \Exception($path . ' not found in json response.');
            }
            $data = $data[$item];
        }
        return $data;
    }
}
