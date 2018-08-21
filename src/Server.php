<?php

namespace MicroShard\JsonRpcServer;

use MicroShard\JsonRpcServer\Exception\RpcException;
use MicroShard\JsonRpcServer\Security\AuthenticatorInterface;
use Psr\Http\Message\ServerRequestInterface;

class Server
{
    const HTTP_OK = 200;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_NOT_FOUND = 404;
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * @var Directory
     */
    protected $directory;

    /**
     * @var AuthenticatorInterface
     */
    protected $authenticator;

    /**
     * @param integer $status
     * @param array $data
     * @return Response
     */
    public static function getResponse($status, array $data)
    {
        $response = new Response();
        $response->setContent(json_encode($data));
        $response->setCode($status);
        $response->addHeader("Content-Type", "application/json");
        return $response;
    }

    /**
     * Server constructor.
     * @param Directory $directory
     * @param AuthenticatorInterface $authenticator
     */
    public function __construct(Directory $directory, AuthenticatorInterface $authenticator)
    {
        $this->directory = $directory;
        $this->authenticator = $authenticator;
    }

    /**
     * @param ServerRequestInterface $httpRequest
     * @return Request
     * @throws RpcException
     */
    protected function getRpcRequest(ServerRequestInterface $httpRequest): Request
    {
        $rawData = $httpRequest->getBody();
        $data = json_decode($rawData, true);

        if ($data == false) {
            throw RpcException::create('malformed request json', ErrorCode::MALFORMED_REQUEST_JSON, self::HTTP_BAD_REQUEST);
        }

        return new Request($data);
    }

    /**
     * @return AuthenticatorInterface
     */
    protected function getAuthenticator(): AuthenticatorInterface
    {
        return $this->authenticator;
    }

    /**
     * @param ServerRequestInterface $httpRequest
     */
    public function run(ServerRequestInterface $httpRequest)
    {
        $result = [
            Response::FIELD_STATUS => self::HTTP_OK,
            Request::FIELD_PAYLOAD => []
        ];

        try {
            $request = $this->getRpcRequest($httpRequest);
            $result[Request::FIELD_RESOURCE] = $request->getResource();
            $result[Request::FIELD_METHOD] = $request->getMethod();
            $result[Request::FIELD_VERSION] = $request->getVersion();
            if ($requestId = $request->getRequestId()) {
                $result[Request::FIELD_REQUEST_ID] = $requestId;
            }

            if ($this->getAuthenticator()->authenticate($request)) {
                $handler = $this->getHandlerForRequest($request);
                $result[Request::FIELD_PAYLOAD] = $handler->handle($request);
                $result[Response::FIELD_MESSAGE] = "OK";
            } else {
                $result[Response::FIELD_STATUS] = self::HTTP_UNAUTHORIZED;
                $result[Response::FIELD_ERROR] = ErrorCode::INVALID_AUTHORIZATION;
                $result[Response::FIELD_MESSAGE] = 'unauthorized';
            }
        } catch (RpcException $exception) {
            $result[Response::FIELD_STATUS] = $exception->getStatusCode();
            $result[Response::FIELD_ERROR] = $exception->getCode();
            $result[Response::FIELD_MESSAGE] = $exception->getMessage();
            $result[Request::FIELD_PAYLOAD] = $exception->getErrorPayload();
        } catch (\Exception $exception) {
            $result[Response::FIELD_STATUS] = self::HTTP_INTERNAL_SERVER_ERROR;
            $result[Response::FIELD_ERROR] = ErrorCode::ERROR_CODE_UNKNOWN;
            $result[Response::FIELD_MESSAGE] = $exception->getMessage();
        }

        self::getResponse(self::HTTP_OK, $result)->send();
    }

    /**
     * @param Request $request
     * @return HandlerInterface
     * @throws RpcException
     */
    protected function getHandlerForRequest(Request $request)
    {
        return $this->directory->getHandler(
            $request->getResource(),
            $request->getMethod(),
            $request->getVersion()
        );
    }
}
