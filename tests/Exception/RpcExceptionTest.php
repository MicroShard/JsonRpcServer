<?php

namespace MicroShard\JsonRpcServer\Test\Exception;

use MicroShard\JsonRpcServer\Exception\RpcException;
use MicroShard\JsonRpcServer\Test\TestCase;

class RpcExceptionTest extends TestCase
{
    public function testGeneral()
    {
        $payload = ['some' => 'data'];
        $exception = RpcException::create('message', 100, 200, $payload);

        $this->assertEquals('message', $exception->getMessage());
        $this->assertEquals(100, $exception->getCode());
        $this->assertEquals(200, $exception->getStatusCode());
        $this->assertEquals($payload, $exception->getErrorPayload());
    }

}