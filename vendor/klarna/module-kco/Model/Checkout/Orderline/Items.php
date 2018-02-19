<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Model\Checkout\Orderline;

use Klarna\Core\Helper\ConfigHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Tax\Model\Calculation;

/**
 * Generate order lines for order items
 *
 * @author     Joe Constant <joe.constant@klarna.com>
 */
class Items extends \Klarna\Core\Model\Checkout\Orderline\Items
{
    /**
     * Items constructor.
     *
     * @param ConfigHelper         $helper
     * @param Calculation          $calculator
     * @param ScopeConfigInterface $config
     * @param EventManager         $eventManager
     * @param UrlInterface         $urlBuilder
     */
    public function __construct(
        ConfigHelper $helper,
        Calculation $calculator,
        ScopeConfigInterface $config,
        EventManager $eventManager,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($helper, $calculator, $config, $eventManager, $urlBuilder);
        $this->eventPrefix = 'kco_';
    }
}
