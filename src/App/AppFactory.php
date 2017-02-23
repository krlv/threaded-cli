<?php
namespace Threaded\App;

class AppFactory
{
    /**
     * @param string $name
     * @param string|null $version
     * @param array $config
     *
     * @return ThreadedApp
     */
    public static function createConsoleApp(string $name, string $version = null, array $config = [])
    {
        return (new ThreadedApp($name, $version, $config))
            ->registerServiceProviders()
            ->registerCommands()
        ;
    }
}
