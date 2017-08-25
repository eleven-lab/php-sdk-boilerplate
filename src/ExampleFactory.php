<?php namespace SDK;

use SDK\Base\Factory as BaseFactory;

/**
 * Class API
 * @package itTaxi
 */
class ExampleFactory extends BaseFactory
{
    /**
     * @var string
     */
    protected $actions_namespace = 'SDK\Actions';

    /**
     * @var array
     */
    protected $actions = [
        'get.kittens'               => 'GetKittens',
        'get.kitten'                => 'GetKitten'
    ];
}