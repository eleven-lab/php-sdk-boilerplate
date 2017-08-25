<?php

namespace ElevenLab\API\Boilerplate;


interface FakeableAction
{

    /**
     * @param $context
     * @param $cache
     * @param $responseStatusCode
     * @param $responseJsonBody
     * @return mixed
     */
    public static function fake($context, $cache, $responseStatusCode, $responseJsonBody);

}