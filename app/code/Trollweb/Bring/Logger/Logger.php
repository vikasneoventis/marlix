<?php
namespace Trollweb\Bring\Logger;

class Logger extends \Monolog\Logger
{
    private $config;

    public function __construct(
        \Trollweb\Bring\Helper\Config $config,
        $name,
        array $handlers = array(),
        array $processors = array()
    ) {
        parent::__construct($name, $handlers, $processors);
        $this->config = $config;
    }

    public function debug($message, array $context = array()) {
        if (!$this->config->debugLoggingEnabled()) {
            return false;
        }

        return parent::debug($message, $context);
    }
}
