<?php

namespace MicroShard\JsonRpcServer\Security;

use MicroShard\JsonRpcServer\Request;

class StaticTokenAuthenticator implements AuthenticatorInterface
{
    const TOKEN_FIELD = 'token';

    /**
     * @var string
     */
    private $staticToken;

    /**
     * StaticTokenAuthenticator constructor.
     * @param string $staticToken
     */
    public function __construct(string $staticToken)
    {
        $this->staticToken = $staticToken;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function authenticate(Request $request): bool
    {
        $valid = false;
        $auth = $request->getAuth();
        if (is_array($auth) && isset($auth[self::TOKEN_FIELD])){
            $valid = $this->staticToken == $auth[self::TOKEN_FIELD];
        }

        return $valid;
    }
}