<?php namespace SDK\Objects;

use SDK\Base\Object;

class Kitten extends Object
{

    protected $attributes = [
        'name' => null,
        'color' => null,
        'age' => null,
        'gender' => null,
        'vaccinated' => null
    ];

    public static function getValidationRules($parent = '')
    {

        return [
            'name' => 'required|string|max:32',
            'color' => 'required|string|in:grey,red,black,white',
            'age' => 'required|integer|min:0|max:10',
            'gender' => 'required|string|in:male,female',
            'vaccinated' => 'required|boolean',
            'owner' => 'sometimes|array'
        ];

    }

    public static function getRelations()
    {

        return [
            'owner' => Owner::class
        ];

    }

}