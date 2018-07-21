<?php

namespace MicroShard\JsonRpcServer\Test;

use Closure;
use Exception;
use MicroShard\JsonRpcServer\Exception\RpcException;
use phpmock\MockBuilder;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param Closure $function
     * @return Exception|null
     */
    public function getException(Closure $function)
    {
        $exception = null;
        try {
            $function();
        } catch (Exception $e) {
            $exception = $e;
        }
        return $exception;
    }

    /**
     * @param int $errorCode
     * @param int $status
     * @param RpcException $exception
     */
    public function assertRpcException(int $errorCode, int $status, RpcException $exception)
    {
        $this->assertEquals($errorCode, $exception->getCode());
        $this->assertEquals($status, $exception->getStatusCode());
    }

    /**
     * @param string $methodName
     * @param Closure $mockFunction
     * @return \phpmock\Mock
     */
    public function mockGlobalMethod(string $methodName, Closure $mockFunction)
    {
        $builder = new MockBuilder();
        $builder->setNamespace('MicroShard\JsonRpcServer')
            ->setName($methodName)
            ->setFunction($mockFunction);
        return $builder->build();
    }
}