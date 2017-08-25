<?php

namespace SDK\Callbacks;

use SDK\Objects\Kitten;
use SDK\Objects\Owner;

abstract class KittenOwnerChanged extends Callback
{

    protected $attributes = [
        'kitten' => null,
        'old_owner' => null,
        'new_owner' => null,
    ];

    protected $objects = [
        'kitten' => Kitten::class,
        'old_owner' => Owner::class,
        'new_owner' => Owner::class
    ];

}