<?php

namespace SDK\Base\Parsers;


class JsonParser extends AbstractParser
{

    static $supportedMimeTypes = [
        'application/json',
        'application/x-javascript',
        'text/x-json',
        'text/javascript',
        'text/x-javascript'
    ];

    public function parse($body)
    {
        return json_decode($body, true);
    }

}