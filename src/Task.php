<?php
namespace Threaded;

use Threaded\Couch\Client;
use Threaded\Couch\Exception\ExceptionInterface;

class Task extends \Threaded
{
    /**
     * @var int serial number of task
     */
    private $taskId;

    /**
     * @var int
     */
    private $shardStart;

    /**
     * @var int
     */
    private $shardEnd;

    /**
     * @param int $taskId
     * @param int $shardStart
     * @param int $shardEnd
     */
    public function __construct(int $taskId, int $shardStart, int $shardEnd)
    {
        $this->taskId     = $taskId;
        $this->shardStart = $shardStart;
        $this->shardEnd   = $shardEnd;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        try {
            $couch = new Client('172.17.0.1', 5984, 'root', 'X5ud07rm');

            $limit = 1000;
            $skip  = 0;
            $total = 0;

            $time  = microtime(true);
            do {
                $docs = $this->getChannels($couch, $limit, $skip);
                array_walk($docs, $this->getChanges($couch));

                $total += count($docs);
                $skip  += $limit;
            } while (count($docs) == $limit);

            $time = microtime(true) - $time;
            echo 'Worker ', $this->taskId, ' has processed ', $total, ' channels in ', $time, ' seconds', PHP_EOL;
        } catch (ExceptionInterface $exception) {
            echo $exception->getMessage(), PHP_EOL;
        }
    }

    private function getChannels(Client $couch, int $limit, int $skip): array
    {
        $query = [
            'selector' => [
                '$and' => [
                    [
                        'shard_key'  => [
                            '$gte' => $this->shardStart,
                        ],
                    ],
                    [
                        'shard_key' => [
                            '$lte' => $this->shardEnd,
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            'limit' => $limit,
            'skip'  => $skip,
        ];
        return $couch->findDocuments('channels', $query)['docs'];
    }

    private function getChanges(Client $couch)
    {
        return function (array $channel) use ($couch) {
            $db = sprintf('channel_notifications_t%d_u%d', $channel['team_id'], $channel['user_id']);
            return $couch->getDatabaseChanges($db, ['include_docs' => 'true'])['results'];
        };
    }
}
