<?php
/**
 * This file is part of the Klarna Order Management module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Ordermanagement\Gateway\Command;

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Model\OrderRepository as KlarnaOrderRepository;
use Klarna\Ordermanagement\Model\Api\Factory;
use Klarna\Ordermanagement\Model\Api\Ordermanagement;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository as MageOrderRepository;
use Magento\Store\Model\Store;

abstract class AbstractCommand extends DataObject implements CommandInterface
{
    /**
     * @var KlarnaOrderRepository
     */
    protected $klarnaOrderRepository;

    /**
     * @var Ordermanagement
     */
    protected $om;

    protected $omCache = [];

    /**
     * @var MageQuoteRepository
     */
    protected $mageQuoteRepository;

    /**
     * @var MageOrderRepository
     */
    protected $mageOrderRepository;

    /**
     * @var ConfigHelper
     */
    protected $helper;

    /**
     * @var Factory
     */
    protected $omFactory;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * AbstractCommand constructor.
     *
     * @param KlarnaOrderRepository $klarnaOrderRepository
     * @param MageQuoteRepository   $mageQuoteRepository
     * @param MageOrderRepository   $mageOrderRepository
     * @param ConfigHelper          $helper
     * @param Factory               $omFactory
     * @param MessageManager        $messageManager
     * @param array                 $data
     */
    public function __construct(
        KlarnaOrderRepository $klarnaOrderRepository,
        MageQuoteRepository $mageQuoteRepository,
        MageOrderRepository $mageOrderRepository,
        ConfigHelper $helper,
        Factory $omFactory,
        MessageManager $messageManager,
        array $data = []
    ) {
        parent::__construct($data);
        $this->klarnaOrderRepository = $klarnaOrderRepository;
        $this->mageQuoteRepository = $mageQuoteRepository;
        $this->mageOrderRepository = $mageOrderRepository;
        $this->helper = $helper;
        $this->omFactory = $omFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * AbstractCommand command
     *
     * @param array $commandSubject
     *
     * @return null|Command\ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    abstract public function execute(array $commandSubject);

    /**
     * Get a Klarna order
     *
     * @param $order
     *
     * @return \Klarna\Core\Model\Order
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getKlarnaOrder($order)
    {
        return $this->klarnaOrderRepository->getByOrder($order);
    }

    /**
     * Get api class
     *
     * @param OrderInterface $order
     * @return Ordermanagement
     * @internal param Store $store
     */
    protected function getOmApi(OrderInterface $order)
    {
        $store = $order->getStore();
        if (isset($this->omCache[$store->getId()])) {
            $this->om = $this->omCache[$store->getId()];
            return $this->om;
        }
        $omClass = $this->helper->getOrderMangagementClass($store);
        $this->om = $this->omFactory->create($omClass);
        $this->om->resetForStore($store, $order->getPayment()->getMethod());
        $this->omCache[$store->getId()] = $this->om;

        return $this->om;
    }
}
