<?php
namespace Threaded;

use Threaded\Couch\Client;
use Threaded\Couch\Exception\ExceptionInterface;

class Task extends \Threaded
{
    private $team;

    /**
     * @param int $team Team ID
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
            echo 'Task will work with teams from shard ', $this->team, PHP_EOL;
        } catch (ExceptionInterface $exception) {
            echo $exception->getMessage(), PHP_EOL;
        }
    }
}
