<?php
namespace Threaded\App\Command;

use Cilex\Provider\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Threaded\Couch\Client;
use Threaded\Couch\Exception\ExceptionInterface;

class SingleThread extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('single')
            ->setDescription('Start single-thread worker')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'If set, worker will handle data in debug mode')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = microtime(true);

        try {
            $couch = new Client('172.17.0.1', 5984, 'root', 'X5ud07rm');

            $limit = 1000;
            $skip  = 0;
            $total = 0;

            do {
                $docs = $this->getChannels($couch, $limit, $skip);
                array_walk($docs, $this->getChanges($couch));

                $total += count($docs);
                $skip  += $limit;
            } while (count($docs) == $limit);

            $output->writeln('Single-thread worker has processed ' . $total . ' channels');
        } catch (ExceptionInterface $exception) {
            $output->writeln($exception->getMessage());
        }

        $time = microtime(true) - $time;
        $output->writeln('Single-thread worker finished in ' . $time . ' seconds');
    }

    private function getChannels(Client $couch, int $limit, int $skip): array
    {
        $query = [
            'selector' => ['is_active' => true],
            'limit'    => $limit,
            'skip'     => $skip,
        ];
        return $couch->findDocuments('channels', $query)['docs'];
    }

    private function getChanges(Client $couch): \Closure
    {
        return function (array $channel) use ($couch) {
            $db = sprintf('channel_notifications_t%d_u%d', $channel['team_id'], $channel['user_id']);
            return $couch->getDatabaseChanges($db, ['include_docs' => 'true'])['results'];
        };
    }
}
