<?php

namespace SDK\Base\Callbacks;


interface VerifyCallbacks
{

    /**
     * Verifies the callback signature
     *
     * @param string $plaintext
     * @param string $signature
     * @param string $secret
     * @return mixed
     */
    public function verify($plaintext, $signature, $secret);

}