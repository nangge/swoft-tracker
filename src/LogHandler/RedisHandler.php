<?php declare(strict_types=1);


namespace SwoftTracker\LogHandler;



use Swoft\Redis\Redis;
use function array_column;
use function implode;
use InvalidArgumentException;
use Monolog\Handler\AbstractProcessingHandler;
use ReflectionException;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Co;
use Swoft\Log\Helper\Log;
use Swoft\Log\Logger as SwoftLogger;

/**
 * Class RedisHandler
 *
 * @since 2.0
 */
class RedisHandler extends AbstractProcessingHandler
{
    /**
     * Write log levels
     *
     * @var string
     */
    protected $levels = '';

    /**
     * @var array
     */
    protected $levelValues = [];

    /**
     * Will exec on construct
     */
    public function init(): void
    {
        if (is_array($this->levels)) {
            $this->levelValues = $this->levels;
            return;
        }

        // Levels like 'notice,error'
        if (is_string($this->levels)) {
            $levelNames        = explode(',', $this->levels);
            $this->levelValues = SwoftLogger::getLevelByNames($levelNames);
        }
    }

    /**
     * Write file
     *
     * @param array $records
     *
     * @throws ReflectionException
     * @throws ContainerException
     */
    protected function write(array $records): void
    {
        if (Log::getLogger()->isJson()) {
            $records = array_map([$this, 'formatJson'], $records);
        } else {
            $records = array_column($records, 'formatted');
        }

        $messageText = implode("\n", $records) . "\n";

        if (Co::id() <= 0) {
            throw new InvalidArgumentException('Write log file must be under Coroutine!');
        }

        sgo(function () use ($messageText) {
           Redis::rPush(config('app_name').':log-key', $messageText);
        });
    }
}
