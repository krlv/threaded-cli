<?php
namespace Threaded;

use Threaded\Couch\Client;
use Threaded\Couch\Exception\ExceptionInterface;

class Task extends \Threaded
{
    private $team;

    /**
     * @param int $team
     */
    public function __construct(int $team)
    {
        $this->team = $team;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        try {
            $couch = new Client('172.17.0.1', 5984, 'root', 'X5ud07rm');

            $limit = 100;
            $skip  = 0;
            $total = 0;

            do {
                $docs = $this->getChannels($couch, $limit, $skip);

                $total += count($docs);
                $skip  += $limit;
            } while (count($docs) == $limit);

            echo 'Worker ', $this->team, ' has processed ', $total, ' channels', PHP_EOL;
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
                        'team_id'  => [
                            '$gte' => $this->team * 125,
                        ],
                    ],
                    [
                        'team_id' => [
                            '$lt' => ($this->team + 1) * 125,
                        ],
                    ],
                ],
            ],
            'limit' => $limit,
            'skip'  => $skip,
        ];
        return $couch->findDocuments('channels', $query)['docs'];
    }
}
