<?php

namespace SDK\Callbacks;

use SDK\Base\Callbacks\Callback as BaseCallback;

abstract class Callback extends BaseCallback
{

    const KITTEN_OWNER_CHANGED = 'kitten.owner.changed';

    protected static $callbackTypes = [
        self::KITTEN_OWNER_CHANGED => KittenOwnerChanged::class
    ];

}