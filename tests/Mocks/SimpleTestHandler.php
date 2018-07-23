<?php

namespace MicroShard\JsonRpcServer\Test\Mocks;

use MicroShard\JsonRpcServer\HandlerInterface;
use MicroShard\JsonRpcServer\Request;

class SimpleTestHandler implements HandlerInterface
{
    public $testValue;

    /**
     * SimpleTestHandler constructor.
     * @param $testValue
     */
    public function __construct($testValue)
    {
        $this->testValue = $testValue;
    }

    /**
     * @param Request $request
     * @return array|mixed
     */
    public function handle(Request $request)
    {
        return ['value' => $this->testValue];
    }
}