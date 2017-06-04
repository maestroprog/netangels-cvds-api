<?php

namespace NetAngels;

/**
 * Класс HTTP запросов к NetAngels API.
 */
final class ApiRequest
{
    const API_URL = 'https://panel.netangels.ru/api/cvds/v1';

    private $token;

    public function __construct(ApiToken $token = null)
    {
        $this->token = $token;
    }

    /**
     * Выполняет GET запрос к API.
     * Используется для получения информации с постраничной навигацией.
     *
     * @param $apiMethod
     * @param int $pageNumber
     * @param array $params
     * @return ApiResult
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws ApiException
     */
    public function page($apiMethod, $pageNumber, array $params = [])
    {
        if ($pageNumber > 1) {
            $params = array_merge($params, ['page' => $pageNumber]);
        }
        return $this->get($apiMethod, [], $params);
    }

    /**
     * Выполняет GET запрос к API.
     *
     * @param string $apiMethod Метод API
     * @param array $resource Список аргументов метода
     * @param array $params Ассоциативный ассив GET параметров
     * @return ApiResult
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws ApiException
     */
    public function get($apiMethod, array $resource = [], array $params = [])
    {
        $append = '';
        if (!empty($resource)) {
            $append .= '/' . implode('/', $resource);
        }
        return $this->request('GET', $apiMethod . $append, $params);
    }

    /**
     * Выполняет POST запрос к API.
     *
     * @param string $apiMethod Метод API
     * @param array $data Список POST переменных
     * @param array $resource
     * @return ApiResult
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws ApiException
     */
    public function post($apiMethod, array $data = [], array $resource = [])
    {
        return $this->request('POST', $apiMethod . '/' . implode('/', $resource), [], $data);
    }

    /**
     * Выполняет PATCH запрос к API.
     *
     * @param string $apiMethod
     * @param array $data
     * @param array $resource
     * @return ApiResult
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws ApiException
     */
    public function patch($apiMethod, array $resource, array $data = [])
    {
        return $this->request('PATCH', $apiMethod . '/' . implode('/', $resource), [], $data);
    }

    /**
     * Выполняет DELETE запрос к API.
     *
     * @param $apiMethod
     * @param array $resource
     * @param array $data
     * @return ApiResult
     */
    public function delete($apiMethod, array $resource, array $data = [])
    {
        return $this->request('DELETE', $apiMethod . '/' . implode('/', $resource), [], $data);
    }

    /**
     * Выполняет запрос к API, получает результат, и формирует из него объект @see ApiResult
     *
     * @param string $method HTTP method
     * @param string $apiMethod
     * @param array $queryParams
     * @param array $data
     * @return ApiResult
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws ApiException
     */
    private function request($method, $apiMethod, array $queryParams = [], array $data = [])
    {
        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])) {
            throw new \InvalidArgumentException('Invalid HTTP method: ' . $method);
        }
        $headers = [];
        if (!is_null($this->token)) {
            $headers[] = 'Authorization: Token ' . $this->token;
        }
        $query = '';
        if (!empty($queryParams)) {
            $query .= '?' . http_build_query($queryParams);
        }
        if ($apiMethod !== 'POST') {
            $data = http_build_query($data);
        }
        $resource = curl_init(self::API_URL . '/' . $apiMethod . '/' . $query);
        curl_setopt_array($resource, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'PHP/NetAngels API library',
        ]);
        $response = curl_exec($resource);
        if (false === $response) {
            throw new \RuntimeException('curl_exec() failed.');
        }
        // delete debug
        /*file_put_contents(
            $method . str_replace(['/', '?', '&', '='], '-', $apiMethod) . '.log',
            date('[d.m.Y H:i:s] ') . var_export($data, true) . $response . "\r\n\r\n\r\n\r\n",
            FILE_APPEND
        );*/
        do {
            list($header, $response) = explode("\r\n\r\n", $response, 2);
            $headerRows = explode("\r\n", $header);
            $status = [];
            if (!preg_match('/^(HTTP\/[0-9\.]{1,})\s{1,}([0-9]{1,})\s{1,}(.*?)$/', $headerRows[0], $status)) {
                throw new \InvalidArgumentException('Invalid response headers: ' . $header);
            }
        } while (100 == $status[2]);
        $code = floor($status[2] / 100);
        if (4 == $code || 5 == $code) {
            throw new ApiException($response, $status[2]);
        }
        return new ApiResult((int)$status[2], $response);
    }
}
