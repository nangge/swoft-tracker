<?php declare(strict_types=1);
/**
 *
 * 获取sql
 *
 */

namespace SwoftTracker\Listener;

use Swoft\Db\Connection\Connection;
use Swoft\Db\DbEvent;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Stdlib\Helper\StringHelper;

/**
 * Class RawSqlListener
 *
 * @since 2.0
 *
 * @Listener(DbEvent::SQL_RAN)
 */
class RawSqlListener implements EventHandlerInterface
{
    /**
     * SQL ran
     *
     * @param EventInterface $event
     *
     */
    public function handle(EventInterface $event): void
    {
        /** @var Connection $connection */
        $connection = $event->getTarget();

        $querySql = $event->getParam(0);
        $bindings = $event->getParam(1);

        $rawSql = $connection->getRawSql($querySql, $bindings);

        $sql = context()->get('sql', []);
        $sql[] = $rawSql;
        context()->set('sql', $sql);
    }
}
