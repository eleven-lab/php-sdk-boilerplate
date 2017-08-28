<?php namespace SDK\Base\Parsers;

interface Parser
{

    public static function isInstance($mimeType);

    public function parse($body);

}