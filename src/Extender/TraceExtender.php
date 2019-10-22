<?php


namespace SwoftTracker\Extender;


use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Rpc\Client\Contract\ExtenderInterface;

/**
 * Class Extender
 *
 * @since 2.0
 *
 * @Bean()
 */
class TraceExtender implements ExtenderInterface
{

    public function getExt(): array
    {
        if (!$traceid = context()->get('traceid', '')) {
            $traceid = uniqid(config('app_name', ''));
            context()->set('traceid', $traceid);
        }

        if (!$spanid = context()->get('spanid', '')) {
            $spanid = config('app_name', '');
            context()->set('spanid', $spanid);
        }
        
        $parentid = context()->get('parentid', '');

        return [
            $traceid,
            $spanid,
            $parentid,
        ];
    }
}
