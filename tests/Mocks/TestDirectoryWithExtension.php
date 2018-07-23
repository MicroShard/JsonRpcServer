<?php

namespace MicroShard\JsonRpcServer\Test\Mocks;

use Closure;
use MicroShard\JsonRpcServer\Directory;
use MicroShard\JsonRpcServer\HandlerInterface;

class TestDirectoryWithExtension extends Directory
{

    /**
     * @param string $resource
     * @param string $method
     * @param string $version
     * @param Closure $anotherConstructor
     */
    public function addHandlerExtension(string $resource, string $method, string $version, Closure $anotherConstructor)
    {
        $this->initDefinition($resource, $method, $version);
        $this->definitions[$resource][$method][$version]['extension'] = $anotherConstructor;
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
        $anotherConstructor = $definition['extension'];
        return $anotherConstructor();
    }
}