<?php
namespace Threaded\App;

use Cilex\Application;
use Threaded\App\Command\SingleThread;
use Threaded\App\Command\MultiThreaded;

class ThreadedApp extends Application
{
    public function registerServiceProviders()
    {
        return $this;
    }

    public function registerCommands()
    {
        $this->command(new MultiThreaded());
        $this->command(new SingleThread());

        return $this;
    }
}
