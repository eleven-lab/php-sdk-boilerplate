<?php namespace SDK\Objects;

use SDK\Base\Object;

class Owner extends Object
{

    protected $attributes = [
        'name' => null,
        'surname' => null,
        'address' => null
    ];

    public static function getValidationRules($parent = '')
    {

        return [
            'name' => 'required|string|max:32',
            'surname' => 'required|string|max:32',
            'address' => 'string',
        ];

    }

}