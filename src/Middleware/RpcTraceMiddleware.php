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

    public function startRpc(RequestInterface $request)
    {
        context()->set('startTime', microtime(true));
        context()->set('version', $request->getVersion());
        context()->set('interface', $request->getInterface());
        context()->set('method', $request->getMethod());
        $params = [
            'query' => $request->getParams(),
            'sql' => context()->get('sql', '')
        ];
        context()->set('params', $params);
        context()->set('appInfo', [
            'env' => config('env'),
            'name' => config('name'),
            'version' => config('version'),
        ]);
    }

    public function endRpc()
    {
        //计算耗时时间
        $cost = sprintf('%.2f', (microtime(true) - context()->get('startTime')) * 1000);
        context()->set('cost', $cost . 'ms');

        //获取执行sql
        $params = context()->get('params');
        $params['sql'] = context()->get('sql', '');
        context()->set('params', $params);

        Log::info(sprintf("【%s】服务，RPC 请求结束", config('name', 'swoft')));
    }
}
