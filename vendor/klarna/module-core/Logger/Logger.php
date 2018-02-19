<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Logger;

use Klarna\Core\Helper\ConfigHelper;
use Magento\Store\Model\StoreManagerInterface;
use Monolog\Logger as MonoLogger;

class Logger extends MonoLogger
{
    /**
     * @var ConfigHelper
     */
    protected $helper;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    protected $store;

    /**
     * Logger constructor.
     *
     * @param string                              $name
     * @param ConfigHelper                        $helper
     * @param StoreManagerInterface               $storeManager
     * @param \Monolog\Handler\HandlerInterface[] $handlers
     * @param \callable[]                         $processors
     */
    public function __construct($name, ConfigHelper $helper, StoreManagerInterface $storeManager, array $handlers = [], array $processors = [])
    {
        parent::__construct($name, $handlers, $processors);
        $this->helper = $helper;
        $this->store = $storeManager->getStore();
    }

    /**
     * @inheritdoc
     */
    public function addRecord($level, $message, array $context = [])
    {
        if (!$this->helper->getApiConfigFlag('debug', $this->store)) {
            return false;
        }
        return parent::addRecord($level, $message, $context);
    }
}
