<?php

namespace MicroShard\JsonRpcServer\Test\Mocks;

use MicroShard\JsonRpcServer\HandlerInterface;
use MicroShard\JsonRpcServer\Request;

class SimpleTestHandler implements HandlerInterface
{
    /**
     * @var string
     */
    public $testValue;

    /**
     * @var bool
     */
    public $throwException = false;

    /**
     * SimpleTestHandler constructor.
     * @param $testValue
     */
    public function __construct(string $testValue)
    {
        $this->testValue = $testValue;
    }

    public function throwExceptionOnHandle()
    {
        $this->throwException = true;
    }

    /**
     * @param Request $request
     * @return array|mixed
     * @throws \Exception
     */
    public function handle(Request $request)
    {
        if ($this->throwException) {
            throw new \Exception('some error');
        }
        return ['value' => $this->testValue];
    }
}