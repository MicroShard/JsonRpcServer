<?php

namespace MicroShard\JsonRpcServer\Test;

use MicroShard\JsonRpcServer\Directory;
use MicroShard\JsonRpcServer\ErrorCode;
use MicroShard\JsonRpcServer\Server;
use MicroShard\JsonRpcServer\Test\Mocks\SimpleTestHandler;
use MicroShard\JsonRpcServer\Test\Mocks\TestDirectory;
use MicroShard\JsonRpcServer\Test\Mocks\TestDirectoryWithExtension;

class DirectoryTest extends TestCase
{

    public function testAddHandler()
    {
        $directory = new Directory();
        $handler1 = new SimpleTestHandler('ab1');
        $handler2 = new SimpleTestHandler('ab2');

        $directory->addHandler('a', 'b', 1, $handler1);
        $directory->addHandler('a', ['b', 'c'], 2, $handler2);

        $this->assertTrue($directory->hasHandler('a', 'b', 1));
        $this->assertFalse($directory->hasHandler('a', 'c', 1));
        $this->assertTrue($directory->hasHandler('a', 'b', 2));
        $this->assertTrue($directory->hasHandler('a', 'c', 2));
        $this->assertFalse($directory->hasHandler('a', 'b', 3));

        $this->assertEquals($handler1, $directory->getHandler('a', 'b', 1));
        $this->assertEquals($handler2, $directory->getHandler('a', 'b', 2));
        $this->assertEquals($handler2, $directory->getHandler('a', 'c', 2));
        $this->assertEquals($handler2, $directory->getHandler('a', 'b', 'latest'));
    }

    public function testAddHandlerDefinition()
    {
        $directory = new Directory();

        $directory->addHandlerDefinition('a', 'b', 1, function(){
            return new SimpleTestHandler('ab1');
        });
        $directory->addHandlerDefinition('a', ['b', 'c'], 2, function(){
            return new SimpleTestHandler('ab2');
        });

        $this->assertTrue($directory->hasHandler('a', 'b', 1));
        $this->assertFalse($directory->hasHandler('a', 'c', 1));
        $this->assertTrue($directory->hasHandler('a', 'b', 2));
        $this->assertTrue($directory->hasHandler('a', 'c', 2));
        $this->assertFalse($directory->hasHandler('a', 'b', 3));

        $this->assertEquals('ab1', $directory->getHandler('a', 'b', 1)->testValue);
        $this->assertEquals('ab2', $directory->getHandler('a', 'b', 2)->testValue);
        $this->assertEquals('ab2', $directory->getHandler('a', 'c', 2)->testValue);
        $this->assertEquals('ab2', $directory->getHandler('a', 'b', 'latest')->testValue);
    }

    public function testExceptions()
    {
        $directory = new TestDirectory();

        $directory->injectDefinition('no', [
            'extension' => [
                1 => [
                    'field' => 'value'
                ]
            ]
        ]);
        $directory->addHandlerDefinition('with', 'exception', 1, function (){
            throw new \Exception('some exception');
        });
        $directory->addHandlerDefinition('with', 'invalid', 1, function (){
            return null;
        });

        $this->assertRpcException(ErrorCode::INVALID_API_PATH, Server::HTTP_NOT_FOUND, $this->getException(function () use ($directory) {
            $directory->getHandler('no', 'found', 1);
        }));

        $this->assertRpcException(ErrorCode::FAULTY_CONSTRUCTOR_HANDLER, Server::HTTP_INTERNAL_SERVER_ERROR, $this->getException(function () use ($directory) {
            $directory->getHandler('with', 'exception', 1);
        }));
        $this->assertRpcException(ErrorCode::INVALID_CONSTRUCTOR_HANDLER, Server::HTTP_INTERNAL_SERVER_ERROR, $this->getException(function () use ($directory) {
            $directory->getHandler('with', 'invalid', 1);
        }));

        $this->assertTrue($directory->hasHandler('no', 'extension', 1));
        $this->assertRpcException(ErrorCode::INVALID_EXTENSION_HANDLER, Server::HTTP_INTERNAL_SERVER_ERROR, $this->getException(function () use ($directory) {
            $directory->getHandler('no', 'extension', 1);
        }));
    }

    public function testWithExtension()
    {
        $directory = new TestDirectoryWithExtension();
        $directory->addHandlerExtension('a', 'b', 1, function(){
           return new SimpleTestHandler('ab1');
        });
        $directory->addHandlerExtension('a', 'c', 1, function(){
            return null;
        });
        $directory->addHandlerExtension('a', 'd', 1, function(){
            throw new \Exception('some exception');
        });

        $this->assertTrue($directory->hasHandler('a', 'b', 1));
        $this->assertTrue($directory->hasHandler('a', 'c', 1));
        $this->assertTrue($directory->hasHandler('a', 'd', 1));

        $this->assertEquals('ab1', $directory->getHandler('a', 'b', 1)->testValue);

        $this->assertRpcException(ErrorCode::INVALID_EXTENSION_HANDLER, Server::HTTP_INTERNAL_SERVER_ERROR, $this->getException(function () use ($directory) {
            $directory->getHandler('a', 'c', 1);
        }));

        $this->assertRpcException(ErrorCode::FAULTY_EXTENSION_HANDLER, Server::HTTP_INTERNAL_SERVER_ERROR, $this->getException(function () use ($directory) {
            $directory->getHandler('a', 'd', 1);
        }));
    }
}