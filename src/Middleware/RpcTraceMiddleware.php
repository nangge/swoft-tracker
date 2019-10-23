<?php declare(strict_types=1);


namespace SwoftTracker\Middleware;


use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Log\Helper\Log;
use Swoft\Rpc\Server\Contract\MiddlewareInterface;
use Swoft\Rpc\Server\Contract\RequestHandlerInterface;
use Swoft\Rpc\Server\Contract\RequestInterface;
use Swoft\Rpc\Server\Contract\ResponseInterface;

/**
 * Class RpcTraceMiddleware
 *
 * @since 2.0
 *
 * @Bean()
 */
class RpcTraceMiddleware implements MiddlewareInterface
{
    /**
     * @param RequestInterface        $request
     * @param RequestHandlerInterface $requestHandler
     *
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface
    {
        $this->startRpc($request);
        $response = $requestHandler->handle($request);
        $this->endRpc();

        return $response;
    }

    public function startRpc(RequestInterface $request) {
        context()->set('startTime', microtime(true));
        context()->set('version', $request->getVersion());
        context()->set('interface', $request->getInterface());
        context()->set('method', $request->getMethod());
        context()->set('params', $request->getParams());
    }

    public function endRpc() {
        $cost         = sprintf('%.2f', (microtime(true)-context()->get('startTime')) * 1000);
        context()->set('cost', $cost . 'ms');
        Log::info('RPC END');
    }
}
