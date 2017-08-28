<?php namespace SDK\Objects;

use SDK\Base\Object;

class Kitten extends Object
{

    /**
     * Object attributes, initialized with their default values
     *
     * @var array
     */
    protected $attributes = [
        'name' => null,
        'color' => null,
        'age' => null,
        'gender' => null,
        'vaccinated' => null,
        'owner' => null,
        'date_of_birth' => -1,
    ];

    /**
     * Here you can specify dates attributes, that get casted automatically
     *
     * @var array
     */
    protected $dates = [
        'created_at'
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