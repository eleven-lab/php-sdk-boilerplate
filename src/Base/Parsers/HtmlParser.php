<?php

namespace SDK\Base\Parsers;


class HtmlParser extends AbstractParser
{

    static $supportedMimeTypes = [
        'text/html'
    ];

    public function parse($body)
    {
        return $body;
    }

}