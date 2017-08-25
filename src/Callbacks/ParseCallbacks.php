<?php

namespace ElevenLab\API\Boilerplate\Callbacks;


interface ParseCallbacks
{

    /**
     *
     * Parse the callback payload and casts it to the corresponding Callback class
     *
     * @param array $body
     * @param array $headers
     * @return mixed
     */
    public function parse($body, $headers);

}