<?php

namespace MicroShard\JsonRpcServer\Test\Security;

use MicroShard\JsonRpcServer\Request;
use MicroShard\JsonRpcServer\Security\StaticTokenAuthenticator;
use MicroShard\JsonRpcServer\Test\TestCase;

class StaticTokenAuthenticatorTest extends TestCase
{

    public function testAuthenticate()
    {
        $token = "123456";
        $authenticator = new StaticTokenAuthenticator($token);

        $request = new Request([
            Request::FIELD_RESOURCE => 'testResource',
            Request::FIELD_METHOD => 'testMethod',
            Request::FIELD_AUTH => [
                'token' => $token
            ]
        ]);
        $this->assertTrue($authenticator->authenticate($request));

        $request = new Request([
            Request::FIELD_RESOURCE => 'testResource',
            Request::FIELD_METHOD => 'testMethod',
            Request::FIELD_AUTH => [
                'token' => '654321'
            ]
        ]);
        $this->assertFalse($authenticator->authenticate($request));
    }

}