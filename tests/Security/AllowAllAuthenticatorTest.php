<?php

namespace MicroShard\JsonRpcServer\Test\Security;

use MicroShard\JsonRpcServer\Request;
use MicroShard\JsonRpcServer\Security\AllowAllAuthenticator;
use MicroShard\JsonRpcServer\Test\TestCase;

class AllowAllAuthenticatorTest extends TestCase
{

    public function testAuthenticate()
    {
        $authenticator = new AllowAllAuthenticator();

        $request = new Request([
            Request::FIELD_RESOURCE => 'testResource',
            Request::FIELD_METHOD => 'testMethod'
        ]);
        $this->assertTrue($authenticator->authenticate($request));
    }

}