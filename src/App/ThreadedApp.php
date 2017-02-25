<?php
namespace Threaded\App;

use Cilex\Application;
use Threaded\App\Command\SingleThread;
use Threaded\App\Command\Threaded;

class ThreadedApp extends Application
{
    public function registerServiceProviders()
    {
        return $this;
    }

    public function registerCommands()
    {
        $this->command(new Threaded());
        $this->command(new SingleThread());

        return $this;
    }
}
