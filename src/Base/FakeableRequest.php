<?php

namespace SDK\Base;


interface FakeableRequest
{

    /**
     * @param $statusCode
     * @param $body
     * @param $base_url
     * @param $timeout
     * @param $payload_type
     * @param $method
     * @param $endpoint
     * @param array $headers
     * @param $payload
     * @param callable $authenticate
     * @param array $auth
     * @return mixed
     */
    public static function fake(
        $statusCode, $body,
        $base_url, $timeout, $payload_type,
        $method, $endpoint, array $headers,
        $payload, callable $authenticate, $auth = []
    );

}