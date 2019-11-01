<?php declare(strict_types=1);


namespace SwoftTracker\Middleware;


use Psr\Http\Message\ServerRequestInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Log\Helper\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Http\Server\Contract\MiddlewareInterface;

/**
 * Class HttpTraceMiddleware
 *
 * @since 2.0
 *
 * @Bean()
 */
class HttpTraceMiddleware implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface        $request
     * @param RequestHandlerInterface $requestHandler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface
    {
        $this->startRequest($request);
        $response = $requestHandler->handle($request);
        $this->endRequest();

        return $response;
    }

    public function startRequest(ServerRequestInterface $request) {
        context()->set('startTime', microtime(true));
        context()->set('interface', $request->getUri()->getPath());
        context()->set('method', $request->getMethod());
        context()->set('params', $request->getQueryParams());
        context()->set('appInfo', [
            'env'     => config('env'),
            'name'    => config('name'),
            'version' => config('version'),
        ]);
    }

    public function endRequest() {
        $cost         = sprintf('%.2f', (microtime(true)-context()->get('startTime')) * 1000);
        context()->set('cost', $cost . 'ms');
        Log::info('HTTP END');
    }
}
