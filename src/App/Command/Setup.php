<?php
namespace Threaded\App\Command;

use Cilex\Provider\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Threaded\Couch\Client;
use Threaded\Couch\Exception\NotFoundException;

class Setup extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('setup')
            ->setDescription('Start setup routine')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'If set, worker will setup data in debug mode')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $couch = new Client('172.17.0.1', 5984, 'root', 'X5ud07rm');

        try {
            $couch->getDatabase('channels');
        } catch (NotFoundException $exception) {
            $couch->createDatabase('channels', ['query' => ['n' => 3, 'q' => 8]]);
            $output->writeln('Database \'channels\' was created');

            $couch->createDesignDocument('channels', 'channel', [
                'views' => [
                    'active' => [
                        'map' => 'function (doc) {
                    if (doc.is_active == 1) {
                        emit([doc.team_id, doc.user_id], doc);
                    }
                }',
                    ],
                ],
                'language' => 'javascript',
            ]);
            $output->writeln('Design document \'channels\' was created for database \'channels\'');
        }

        try {
            $couch->getDatabase('channel_notifications');
        } catch (NotFoundException $exception) {
            $couch->createDatabase('channel_notifications', ['query' => ['n' => 3, 'q' => 8]]);
            $output->writeln('Database \'channel_notifications\' was created');
        }

        for ($i = 0; $i < 100000; ++$i) {
            $team = random_int(1, 100000);
            $user = random_int(1, 100000);

            $channels = [];
            for ($j = 1, $n = random_int(1, 4); $j < $n; ++$j) {
                $channels[] = [
                    'shard_key'  => $team % 255,
                    'channel_id' => sprintf('channel%d_team%d_user%d', $j, $team, $user),
                    'team_id'    => $team,
                    'user_id'    => $user,
                    'title'      => sprintf('Channel #%d (team: %d, user: %d)', $j, $team, $user),
                    'is_active'  => true,
                ];
            }
            $couch->bulkDocuments('channels', $channels);

            array_walk($channels, function (array $channel) use ($couch, $output) {
                $unread = random_int(1, 10);

                $couch->createDocument('channel_notifications', [
                    '_id'    => $channel['channel_id'],
                    'unread' => $unread,
                ]);

                $db = sprintf('channel_notifications_t%d_u%d', $channel['team_id'], $channel['user_id']);

                try {
                    $couch->getDatabase($db);
                } catch (NotFoundException $exception) {
                    $couch->createDatabase($db, ['query' => ['n' => 3, 'q' => 8]]);
                    $output->writeln('Database \'' . $db . '\' was created');
                }

                $couch->createDocument($db, ['unread' => $unread]);
            });
        }
    }
}
