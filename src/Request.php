<?php

namespace MicroShard\JsonRpcServer;

use MicroShard\JsonRpcServer\Exception\RpcException;

class Request
{
    const FIELD_RESOURCE = 'resource';
    const FIELD_METHOD = 'method';
    const FIELD_VERSION = 'version';
    const FIELD_PAYLOAD = 'payload';
    const FIELD_AUTH = 'auth';

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param array $data
     * @throws RpcException
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;

        if (!isset($data[self::FIELD_RESOURCE])){
            throw RpcException::create('missing resource', ErrorCode::MISSING_RESOURCE, Server::HTTP_BAD_REQUEST);
        }
        $this->resource = $data[self::FIELD_RESOURCE];

        if (!isset($data[self::FIELD_METHOD])){
            throw RpcException::create('missing method', ErrorCode::MISSING_METHOD, Server::HTTP_BAD_REQUEST);
        }
        $this->method = $data[self::FIELD_METHOD];
    }

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return (isset($this->data[self::FIELD_PAYLOAD]))
            ? $this->data[self::FIELD_PAYLOAD]
            : [];
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return (isset($this->data[self::FIELD_VERSION]))
            ? $this->data[self::FIELD_VERSION]
            : Directory::VERSION_LATEST;
    }

    /**
     * @return array
     */
    public function getAuth(): array
    {
        return (isset($this->data[self::FIELD_AUTH]))
            ? $this->data[self::FIELD_AUTH]
            : [];
    }
}