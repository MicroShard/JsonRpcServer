<?php

namespace MicroShard\JsonRpcServer\Test;

use MicroShard\JsonRpcServer\Directory;
use MicroShard\JsonRpcServer\ErrorCode;
use MicroShard\JsonRpcServer\Exception\RpcException;
use MicroShard\JsonRpcServer\Request;
use MicroShard\JsonRpcServer\Server;


class RequestTest extends TestCase
{

    public function testMissingFields()
    {
        $exception = $this->getException(function(){
            $data = [
                Request::FIELD_RESOURCE => 'testResource'
            ];
            new Request($data);
        });
        $this->assertInstanceOf(RpcException::class, $exception);
        $this->assertRpcException(ErrorCode::MISSING_METHOD, Server::HTTP_BAD_REQUEST, $exception);

        $exception = $this->getException(function(){
            $data = [
                Request::FIELD_METHOD => 'testMethod'
            ];
            new Request($data);
        });
        $this->assertInstanceOf(RpcException::class, $exception);
        $this->assertRpcException(ErrorCode::MISSING_RESOURCE, Server::HTTP_BAD_REQUEST, $exception);
    }

    public function testDataFields()
    {
        $data = [
            Request::FIELD_RESOURCE => 'testResource',
            Request::FIELD_METHOD => 'testMethod',
            Request::FIELD_VERSION => '2',
            Request::FIELD_AUTH => [
                'some' => 'field'
            ],
            Request::FIELD_PAYLOAD => [
                'pay' => 'load'
            ]
        ];

        $request = new Request($data);

        $this->assertEquals('testResource', $request->getResource());
        $this->assertEquals('testMethod', $request->getMethod());
        $this->assertEquals('2', $request->getVersion());
        $this->assertArraySubset(['some' => 'field'], $request->getAuth());
        $this->assertArraySubset(['pay' => 'load'], $request->getPayload());
        $this->assertEquals($data, $request->getData());
    }

    public function testDefaultValues()
    {
        $data = [
            Request::FIELD_RESOURCE => 'testResource',
            Request::FIELD_METHOD => 'testMethod'
        ];

        $request = new Request($data);

        $this->assertEquals('testResource', $request->getResource());
        $this->assertEquals('testMethod', $request->getMethod());
        $this->assertEquals(Directory::VERSION_LATEST, $request->getVersion());
        $this->assertEquals([], $request->getAuth());
        $this->assertEquals([], $request->getPayload());
    }
}