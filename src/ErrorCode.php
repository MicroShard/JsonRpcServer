<?php

namespace MicroShard\JsonRpcServer;

class ErrorCode
{
    const MALFORMED_REQUEST_JSON = 100;
    const MISSING_RESOURCE = 101;
    const MISSING_METHOD = 102;

    const INVALID_AUTHORIZATION = 120;

    const INVALID_API_PATH = 200;
    const UNABLE_TO_PROCESS = 201;

    const ERROR_CODE_UNKNOWN = 999;
}