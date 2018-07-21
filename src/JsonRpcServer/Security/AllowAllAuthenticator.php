<?php

namespace MicroShard\JsonRpcServer\Security;

use MicroShard\JsonRpcServer\Request;

class AllowAllAuthenticator implements AuthenticatorInterface
{
    /**
     * @param Request $request
     * @return bool
     */
    public function authenticate(Request $request): bool
    {
        return true;
    }
}