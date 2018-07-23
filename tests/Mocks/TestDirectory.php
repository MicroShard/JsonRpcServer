<?php

namespace MicroShard\JsonRpcServer\Test\Mocks;

use MicroShard\JsonRpcServer\Directory;

class TestDirectory extends Directory
{
    /**
     * @param string $resource
     * @param array $definition
     * @return $this
     */
    public function injectDefinition(string $resource, array $definition)
    {
        $this->definitions[$resource] = $definition;
        return $this;
    }
}