<?php

namespace MicroShard\JsonRpcServer\Test;

use MicroShard\JsonRpcServer\Response;
use phpmock\MockBuilder;


class ResponseTest extends TestCase
{

    public function testSend()
    {
        $response = new Response();

        $response->addHeader('SomeHeader', 'SomeValue');
        $response->setCode(666);
        $response->setContent('some content');

        // mock global methods

        // mock http_response_code
        $responseCode = null;
        $httpResponseCodeMock = $this->mockGlobalMethod('http_response_code', function($value) use (&$responseCode) {
            $responseCode = $value;
        });
        $httpResponseCodeMock->enable();

        // mock header
        $responseHeaders = [];
        $httpResponseHeaderMock = $this->mockGlobalMethod('header', function($value) use (&$responseHeaders) {
            $responseHeaders[] = $value;
        });
        $httpResponseHeaderMock->enable();

        $this->expectOutputString('some content');
        $response->send();

        $this->assertEquals(666, $responseCode);
        $this->assertTrue(array_search('Content-Length: 12', $responseHeaders) !== false);
        $this->assertTrue(array_search('SomeHeader: SomeValue', $responseHeaders) !== false);

        $httpResponseCodeMock->disable();
        $httpResponseHeaderMock->disable();
    }
}