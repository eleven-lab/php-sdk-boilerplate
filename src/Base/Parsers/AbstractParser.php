<?php
/**
 * Created by PhpStorm.
 * User: desh
 * Date: 28/08/17
 * Time: 12.10
 */

namespace SDK\Base\Parsers;


use Illuminate\Support\Str;

abstract class AbstractParser implements Parser
{

    static $supportedMimeTypes = [];

    public static function isInstance($mimeType)
    {

        foreach (static::$supportedMimeTypes as $type) {

            if(Str::contains($mimeType, $type)) return true;

        }

        return false;

    }

}