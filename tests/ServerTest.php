<?php

namespace MicroShard\JsonRpcServer\Test;

use MicroShard\JsonRpcServer\Directory;
use MicroShard\JsonRpcServer\Request;
use MicroShard\JsonRpcServer\Security\AllowAllAuthenticator;
use MicroShard\JsonRpcServer\Security\StaticTokenAuthenticator;
use MicroShard\JsonRpcServer\Server;
use MicroShard\JsonRpcServer\Test\Mocks\SimpleTestHandler;
use MicroShard\JsonRpcServer\Test\Mocks\TestHttpRequest;

class ServerTest extends TestCase
{

    protected $httpResponseCodeMock;
    protected $httpResponseHeaderMock;

    public function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        // mock http_response_code
        if (is_null($this->httpResponseCodeMock)) {
            $this->httpResponseCodeMock = $this->mockGlobalMethod('http_response_code', function ($value) {
            });
        }
        $this->httpResponseCodeMock->enable();

        // mock header
        if (is_null($this->httpResponseHeaderMock)) {
            $this->httpResponseHeaderMock = $this->mockGlobalMethod('header', function ($value) {
            });
        }
        $this->httpResponseHeaderMock->enable();
    }

    public function tearDown()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->httpResponseCodeMock->disable();
        $this->httpResponseHeaderMock->disable();
    }

    public function testSuccess()
    {
        $authenticator = new AllowAllAuthenticator();
        $directory = new Directory();
        $handler = new SimpleTestHandler('test result');
        $directory->addHandler('resource_a', 'method_1', 1, $handler);

        $httpRequest = new TestHttpRequest();
        $httpRequest->setBodyData([
            Request::FIELD_RESOURCE => 'resource_a',
            Request::FIELD_METHOD => 'method_1',
            Request::FIELD_REQUEST_ID => '123'
        ]);

        $server = new Server($directory, $authenticator);

        $this->expectOutputString('{"status":200,"payload":{"value":"test result"},"resource":"resource_a","method":"method_1","version":"latest","id":"123","message":"OK"}');
        $server->run($httpRequest);
    }

    public function testMalformedRequest()
    {
        $authenticator = new AllowAllAuthenticator();
        $directory = new Directory();
        $handler = new SimpleTestHandler('test result');
        $directory->addHandler('resource_a', 'method_1', 1, $handler);

        $httpRequest = new TestHttpRequest();
        $httpRequest->setBody('{foo');

        $server = new Server($directory, $authenticator);

        $this->expectOutputString('{"status":400,"payload":[],"error":100,"message":"malformed request json"}');
        $server->run($httpRequest);
    }

    public function testInvalidAuth()
    {
        $authenticator = new StaticTokenAuthenticator('token');
        $directory = new Directory();
        $handler = new SimpleTestHandler('test result');
        $directory->addHandler('resource_a', 'method_1', 1, $handler);

        $httpRequest = new TestHttpRequest();
        $httpRequest->setBodyData([
            Request::FIELD_RESOURCE => 'resource_a',
            Request::FIELD_METHOD => 'method_1',
        ]);

        $server = new Server($directory, $authenticator);

        $this->expectOutputString('{"status":401,"payload":[],"resource":"resource_a","method":"method_1","version":"latest","error":120,"message":"unauthorized"}');
        $server->run($httpRequest);
    }

    public function testException()
    {
        $authenticator = new AllowAllAuthenticator();
        $directory = new Directory();
        $handler = new SimpleTestHandler('test result');
        $handler->throwExceptionOnHandle();
        $directory->addHandler('resource_a', 'method_1', 1, $handler);

        $httpRequest = new TestHttpRequest();
        $httpRequest->setBodyData([
            Request::FIELD_RESOURCE => 'resource_a',
            Request::FIELD_METHOD => 'method_1',
        ]);

        $server = new Server($directory, $authenticator);

        $this->expectOutputString('{"status":500,"payload":[],"resource":"resource_a","method":"method_1","version":"latest","error":999,"message":"some error"}');
        $server->run($httpRequest);
    }
}