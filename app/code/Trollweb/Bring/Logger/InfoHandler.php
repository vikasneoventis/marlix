<?php
namespace Trollweb\Bring\Logger;

class InfoHandler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = \Monolog\Logger::INFO;
    protected $fileName = '/var/log/trollweb_bring.log';
}
