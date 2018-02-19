<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Model\Api;

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Helper\VersionInfo;
use Klarna\Core\Model\Api\BuilderFactory;
use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Klarna\Core\Model\OrderRepository;
use Klarna\Kco\Api\ApiInterface;
use Klarna\Kco\Model\Checkout\Type\Kco;
use Klarna\Kco\Traits\Api;
use Klarna\Kred\Lib\Connector;
use Klarna\Kred\Lib\MagentoOrder;
use Klarna\Kred\Model\Api\Builder\Kred as KredBuilder;
use Klarna\Kred\Model\PushqueueRepository;
use Klarna\Kred\Traits\Logging;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\Order\Invoice;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Api request to the Klarna Kred platform
 */
class Kred extends DataObject implements ApiInterface
{
    use Api, Logging;

    /**
     * @var \Klarna_Checkout_Connector
     */
    protected $_connector;

    /**
     * @var MagentoOrder()
     */
    protected $_order;

    /**
     * If a request is being made recursively, to prevent loops
     *
     * @var bool
     */
    protected $_isRecursiveCall = false;

    /**
     * @var Klarna
     */
    protected $_klarnaOrderManagement;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var PushqueueRepository
     */
    protected $pushqueueRepository;

    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * @var VersionInfo
     */
    protected $versionInfo;

    /**
     * Kred constructor.
     *
     * @param Kco                 $kco
     * @param BuilderFactory      $builderFactory
     * @param ConfigHelper        $configHelper
     * @param LoggerInterface     $log
     * @param ManagerInterface    $eventManager
     * @param OrderRepository     $orderRepository
     * @param PushqueueRepository $pushqueueRepository
     * @param DirectoryHelper     $directoryHelper
     * @param VersionInfo         $versionInfo
     * @param array               $data
     */
    public function __construct(
        Kco $kco,
        BuilderFactory $builderFactory,
        ConfigHelper $configHelper,
        LoggerInterface $log,
        ManagerInterface $eventManager,
        OrderRepository $orderRepository,
        PushqueueRepository $pushqueueRepository,
        DirectoryHelper $directoryHelper,
        VersionInfo $versionInfo,
        array $data = []
    ) {
        parent::__construct($data);
        $this->kco = $kco;
        $this->builderFactory = $builderFactory;
        $this->configHelper = $configHelper;
        $this->log = $log;
        $this->eventManager = $eventManager;
        $this->orderRepository = $orderRepository;
        $this->pushqueueRepository = $pushqueueRepository;
        $this->directoryHelper = $directoryHelper;
        $this->builderType = KredBuilder::class;
        $this->versionInfo = $versionInfo;
    }

    /**
     * Create or update an order in the checkout API
     *
     * @param string     $checkoutId
     * @param bool|false $createIfNotExists
     * @param bool|false $updateItems
     *
     * @return DataObject
     * @throws \Klarna\Core\Exception
     * @throws \Klarna_Checkout_ApiErrorException
     * @throws KlarnaApiException
     */
    public function initKlarnaCheckout($checkoutId = null, $createIfNotExists = false, $updateItems = false)
    {
        $klarnaOrder = new DataObject();
        $order = $this->_getCheckoutOrder($checkoutId, $this->getStore());
        $data = [];

        try {
            if ($createIfNotExists || $updateItems) {
                if (!$checkoutId && $createIfNotExists) {
                    $data = $this->getGeneratedCreateRequest();

                    $createRequest = $order->create($data);
                    $this->_debug($createRequest);

                    $fetchRequest = $order->fetch();
                    $this->_debug($fetchRequest);
                } elseif ($updateItems) {
                    $data = $this->getGeneratedUpdateRequest();
                    $updateRequest = $order->update($data);
                    $this->_debug($updateRequest);
                }
            } elseif ($checkoutId) {
                $fetchRequest = $this->_getCheckoutOrder()->fetch();
                $this->_debug($fetchRequest);
            }

            $klarnaOrder->setData($order->marshal());
            $klarnaOrder->setIsSuccessful(true);
        } catch (\Klarna_Checkout_ApiErrorException $e) {
            if ($data) {
                $this->_debug('Failed init request');
                $this->_debug($data);
            }
            $this->_debug($e->getMessage(), LogLevel::ERROR);
            $this->_debug($e->getPayload(), LogLevel::ERROR);
            $klarnaOrder->setIsSuccessful(false);
        }

        // If existing order fails or is expired, create a new one
        if (!$klarnaOrder->getIsSuccessful() && $createIfNotExists) {
            $data = [];
            try {
                $data = $this->getGeneratedCreateRequest();

                $createRequest = $order->create($data);
                $this->_debug($createRequest);

                $fetchRequest = $order->fetch();
                $this->_debug($fetchRequest);

                $klarnaOrder->setData($order->marshal());
                $klarnaOrder->setIsSuccessful(true);
            } catch (\Klarna_Checkout_ApiErrorException $e) {
                if ($data) {
                    $this->_debug('Failed second attempt init request');
                    $this->_debug($data);
                    $klarnaOrder->addData($data);
                }

                $this->_debug($e->getMessage(), LogLevel::ERROR);
                $this->_debug($e->getPayload(), LogLevel::ERROR);
                $klarnaOrder->setIsSuccessful(false);
                throw $e;
            }
        }

        // If we still get an error, consider giving up
        if (!$klarnaOrder->getIsSuccessful()) {
            if ($klarnaOrder->getHttpStatusCode() == 401) {
                throw new KlarnaApiException(__($klarnaOrder->getHttpStatusMessage()));
            }
            // If "don't create" && "don't update" OR "checkoutId is null"
            if ((!$createIfNotExists && !$updateItems) || !$checkoutId) {
                throw new KlarnaApiException(__('Unable to initialize Klarna checkout order'));
            }
            // Last ditch effort, try just fetching the order
            return $this->initKlarnaCheckout($checkoutId, false, false);
        }

        $this->setKlarnaOrder($klarnaOrder);

        return $klarnaOrder;
    }

    /**
     * Get Klarna checkout order
     *
     * @param string                     $checkoutId
     * @param \Magento\Store\Model\Store $store
     *
     * @return MagentoOrder()
     */
    protected function _getCheckoutOrder($checkoutId = null, $store = null)
    {
        if (is_null($this->_order)) {
            $this->_order = new MagentoOrder($this->_getCheckoutConnector($store), $checkoutId);
        }

        return $this->_order;
    }

    /**
     * Get the checkout connection
     *
     * @param \Magento\Store\Model\Store $store
     *
     * @return \Klarna_Checkout_Connector
     * @throws \Klarna_Checkout_Exception
     */
    protected function _getCheckoutConnector($store = null)
    {
        if (is_null($this->_connector)) {
            $url = $this->configHelper->getApiConfigFlag('test_mode', $store) ? $this->getConfig()->getTestdriveUrl()
                : $this->getConfig()->getProductionUrl();

            $userAgent = new \Klarna_Checkout_UserAgent();
            $userAgent->addField(
                'Magento',
                [
                    'name'    => $this->versionInfo->getMageEdition(),
                    'version' => $this->versionInfo->getMageVersion()
                ]
            );
            $userAgent->addField(
                'MAGE_MODE',
                [
                    'name'    => ucwords($this->versionInfo->getMageMode()),
                    'version' => 'Mode'
                ]
            );
            $userAgent->addField(
                'KcoModule',
                [
                    'name'    => 'core',
                    'version' => $this->versionInfo->getVersion('klarna/module-kco')
                ]
            );
            $userAgent->addField(
                'KredModule',
                [
                    'name'    => 'addon',
                    'version' => $this->versionInfo->getVersion('klarna/module-kred')
                ]
            );
            $this->_connector = Connector::create(
                $this->configHelper->getApiConfig('shared_secret', $store),
                $url,
                $userAgent
            );
        }

        return $this->_connector;
    }

    /**
     * Get Klarna Checkout Reservation Id
     *
     * @return string
     */
    public function getReservationId()
    {
        return $this->getKlarnaOrder()->getReservation();
    }

    /**
     * Get the html snippet for an order
     *
     * @return string
     */
    public function getKlarnaCheckoutGui()
    {
        return $this->getKlarnaOrder()->getData('gui/snippet');
    }

    /**
     * Get the increment id for items on reservation in Klarna api
     *
     * @param Invoice $invoice
     *
     * @return int
     * @deprecated ?????
     */
    protected function _getInvoiceKlarnaIncrementId($invoice)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoices */
        $invoices = $invoice->getOrder()->getInvoiceCollection();

        foreach ($invoices as $key => $_invoice) {
            if ($_invoice->getIncrementId() == $invoice->getIncrementId()) {
                return (int)$key;
            }
        }

        return 0;
    }
}
