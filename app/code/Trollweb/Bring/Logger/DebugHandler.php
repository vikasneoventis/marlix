<?php
namespace Trollweb\Bring\Logger;

class DebugHandler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = \Monolog\Logger::DEBUG;
    protected $fileName = '/var/log/trollweb_bring_debug.log';
}
