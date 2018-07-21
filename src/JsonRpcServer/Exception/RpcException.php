<?php

namespace MicroShard\JsonRpcServer\Exception;

use MicroShard\JsonRpcServer\Server;

class RpcException extends \Exception
{
    /**
     * @var int
     */
    protected $statusCode = Server::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * @var array
     */
    protected $errorPayload = [];

    /**
     * @param string $message
     * @param int $errorCode
     * @param int $statusCode
     * @param array $errorPayload
     * @return RpcException
     */
    public static function create(string $message, int $errorCode = 0, int $statusCode = Server::HTTP_INTERNAL_SERVER_ERROR, array $errorPayload = []): self
    {
        $exception = new self($message, $errorCode);
        $exception->setStatusCode($statusCode);
        $exception->setErrorPayload($errorPayload);
        return $exception;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     * @return RpcException
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrorPayload(): array
    {
        return $this->errorPayload;
    }

    /**
     * @param array $errorPayload
     * @return RpcException
     */
    public function setErrorPayload(array $errorPayload): self
    {
        $this->errorPayload = $errorPayload;
        return $this;
    }
}
