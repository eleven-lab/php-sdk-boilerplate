<?php

namespace SDK\Base\Callbacks;


class Verifier implements VerifyCallbacks
{

    /**
     *
     * Verifies the signature provided
     * Timing attack safe hash comparison
     *
     * @param string $plaintext
     * @param string $signature
     * @param string $secret
     *
     * @return bool
     */
    public function verify($plaintext, $signature, $secret)
    {
        $digest =  base64_encode(hash_hmac('sha256', $plaintext, $secret, true));
        return hash_equals($signature, $digest);
    }
}