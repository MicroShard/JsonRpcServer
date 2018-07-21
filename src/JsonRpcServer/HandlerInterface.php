<?php

namespace MicroShard\JsonRpcServer;

interface HandlerInterface
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function handle(Request $request);
}