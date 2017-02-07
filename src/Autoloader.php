<?php
namespace Threaded;

class Autoloader extends \Worker
{
    /**
     * @var string
     */
    protected $loader;

    /**
     * @param string $loader
     */
    public function __construct(string $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        require_once $this->loader;
    }

    /**
     * {@inheritdoc
     *
     * Override default inheritance behaviour for the new threaded context
     */
    public function start(int $options = NULL)
    {
        return parent::start(PTHREADS_INHERIT_NONE);
    }
}
