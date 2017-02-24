<?php
namespace Threaded\App\Command;

use Cilex\Provider\Console\Command;
use Pool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Threaded\Autoloader;
use Threaded\Task;

class Threaded extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('threaded')
            ->setDescription('Start threaded worker')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'If set, worker will handle data in debug mode')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = microtime(true);

        // number of shards in the application
        $shard = 256;

        // pool size
        $size = 8;
        $pool = new Pool($size, Autoloader::class, [__DIR__ . '/../../../vendor/autoload.php']);

        // each task will process $chunk amount of shards
        $chunk = round($shard / $size, 0, PHP_ROUND_HALF_UP);

        // submit a task to the pool
        for ($i = 0; $i < $size; ++$i) {
            $start = $chunk * $i;
            $end   = $chunk * ($i + 1) - 1;
            $pool->submit(new Task($i, $start, $end));
        }

        // in the real world, do some ::collect somewhere
        // shutdown, because explicit is good
        $pool->shutdown();

        $time = microtime(true) - $time;
        $output->writeln('Multi-thread worker finished in ' . $time . ' seconds');
    }
}
