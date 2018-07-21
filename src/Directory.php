<?php

namespace MicroShard\JsonRpcServer;

use MicroShard\JsonRpcServer\Exception\RpcException;

class Directory
{
    const VERSION_LATEST = 'latest';

    /**
     * @var HandlerInterface[]
     */
    protected $handlers = [];

    /**
     * @var array
     */
    protected $definitions = [];

    /**
     * @param string $resource
     * @param string $method
     * @param string $version
     * @return $this
     */
    protected function initDefinition(string $resource, string $method, string $version): Directory
    {
        if (!isset($this->definitions[$resource])) {
            $this->definitions[$resource] = [];
        }
        if (!isset($this->definitions[$resource][$method])) {
            $this->definitions[$resource][$method] = [
                'latest' => $version
            ];
        } else {
            if ($this->definitions[$resource][$method][self::VERSION_LATEST] < $version) {
                $this->definitions[$resource][$method][self::VERSION_LATEST] = $version;
            }
        }

        //TODO: VALIDATE - overwrite existing?
        $this->definitions[$resource][$method][$version] = [
            'className' => null,
            'handler' => null
        ];
        return $this;
    }

    /**
     * @param string $resource
     * @param string|array $method
     * @param string $version
     * @param string $className
     * @return $this
     */
    public function addDefinition(string $resource, string $method, string $version, string $className): Directory
    {
        if (is_array($method)) {
            foreach ($method as $meth){
                $this->initDefinition($resource, $meth, $version);
                $this->definitions[$resource][$meth][$version]['className'] = $className;
            }
        } else {
            $this->initDefinition($resource, $method, $version);
            $this->definitions[$resource][$method][$version]['className'] = $className;
        }
        return $this;
    }

    /**
     * @param string $resource
     * @param string|array $method
     * @param string $version
     * @param HandlerInterface $handler
     * @return $this
     */
    public function addHandler(string $resource, string $method, string $version, HandlerInterface $handler): Directory
    {
        if (is_array($method)) {
            foreach ($method as $meth) {
                $this->initDefinition($resource, $meth, $version);
                $this->definitions[$resource][$meth][$version]['handler'] = $handler;
            }
        } else {
            $this->initDefinition($resource, $method, $version);
            $this->definitions[$resource][$method][$version]['handler'] = $handler;
        }
        $this->handlers[get_class($handler)] = $handler;
        return $this;
    }

    /**
     * @param string $resource
     * @param string $method
     * @param string $version
     * @return bool
     */
    public function hasHandler(string $resource, string $method, string $version): bool
    {
        return isset($this->definitions[$resource])
            && isset($this->definitions[$resource][$method])
            && isset($this->definitions[$resource][$method][$version]);
    }

    /**
     * @param string $resource
     * @param string $method
     * @param string $version
     * @return HandlerInterface
     * @throws RpcException
     */
    public function getHandler(string $resource, string $method, string $version = self::VERSION_LATEST): HandlerInterface
    {
        $version = ($version == self::VERSION_LATEST)
            ? $this->definitions[$resource][$method][self::VERSION_LATEST]
            : $version;

        if (!$this->hasHandler($resource, $method, $version)) {
            throw RpcException::create("invalid api path: $resource/$method/$version", ErrorCode::INVALID_API_PATH, Server::HTTP_NOT_FOUND);
        }

        $definition = $this->definitions[$resource][$method][$version];
        $handler = null;

        if ($definition['handler']) {
            $handler = $definition['handler'];
        } else if($definition['className']) {
            if (!isset($this->handlers[$definition['className']])) {
                $handler = new $definition['className']();
                $this->handlers[$definition['className']] = $handler;
            } else {
                $handler = $this->handlers[$definition['className']];
            }
        }

        if (is_null($handler)){
            $handler = $this->getHandlerExtended($definition, $resource, $method, $version);
        }

        if (is_null($handler)) {
            throw RpcException::create("unable to process: $resource/$method/$version", ErrorCode::UNABLE_TO_PROCESS, Server::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $handler;
    }

    /**
     * just for overwrite purposes in case you have some additional logic to create handlers
     *
     * @param array $definition
     * @param string $resource
     * @param string $method
     * @param string $version
     * @return null|HandlerInterface
     */
    protected function getHandlerExtended(array $definition, string $resource, string $method, string $version): HandlerInterface
    {
        return null;
    }
}