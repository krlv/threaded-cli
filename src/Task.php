<?php
namespace Threaded;

use Threaded\Couch\Client;
use Threaded\Couch\Exception\ExceptionInterface;

class Task extends \Threaded
{
    private $user;

    /**
     * @param int $user User ID
     */
    public function __construct(int $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        try {
            $divan = new Client('172.17.0.1', 5984, 'root', 'X5ud07rm');
            $divan->createDatabase('timeline_user_' . $this->user);
        } catch (ExceptionInterface $exception) {
            echo $exception->getMessage(), PHP_EOL;
        }
    }
}
