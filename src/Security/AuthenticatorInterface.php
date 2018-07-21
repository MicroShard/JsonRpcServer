<?php

namespace MicroShard\JsonRpcServer\Security;

use MicroShard\JsonRpcServer\Request;

interface AuthenticatorInterface
{
    /**
     * @param Request $request
     * @return bool
     */
    public function authenticate(Request $request): bool;
}