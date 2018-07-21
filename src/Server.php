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
            'status' => self::HTTP_OK,
            'payload' => []
        ];

        try {
            $request = $this->getRpcRequest($httpRequest);
            $result['resource'] = $request->getResource();
            $result['method'] = $request->getMethod();
            $result['version'] = $request->getVersion();

            if ($this->getAuthenticator()->authenticate($request)) {
                $handler = $this->getHandlerForRequest($request);
                $result['payload'] = $handler->handle($request);
                $result['message'] = "OK";
            } else {
                $result['status'] = self::HTTP_UNAUTHORIZED;
                $result['error'] = ErrorCode::INVALID_AUTHORIZATION;
                $result['message'] = 'unauthorized';
            }
        } catch (RpcException $exception) {
            $result['status'] = $exception->getStatusCode();
            $result['error'] = $exception->getCode();
            $result['message'] = $exception->getMessage();
            $result['payload'] = $exception->getErrorPayload();
        } catch (\Exception $exception) {
            $result['status'] = self::HTTP_INTERNAL_SERVER_ERROR;
            $result['error'] = ErrorCode::ERROR_CODE_UNKNOWN;
            $result['message'] = $exception->getMessage();
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
