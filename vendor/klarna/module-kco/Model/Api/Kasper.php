<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Model\Api;

use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Klarna\Core\Model\Api\BuilderFactory;
use Klarna\Kco\Api\ApiInterface;
use Klarna\Kco\Helper\Checkout as CheckoutHelper;
use Klarna\Kco\Model\Api\Builder\Kasper as KasperBuilder;
use Klarna\Kco\Model\Api\Rest\Service\Checkout;
use Klarna\Kco\Model\Checkout\Type\Kco;
use Klarna\Kco\Traits\Api;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Api request to the Klarna Kasper platform
 */
class Kasper extends DataObject implements ApiInterface
{
    use Api;

    /**
     * If a request is being made recursively, to prevent loops
     *
     * @var bool
     */
    protected $isRecursiveCall = false;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var Checkout
     */
    protected $checkout;

    /**
     * Kasper constructor.
     *
     * @param CheckoutHelper $checkoutHelper
     * @param Kco            $kco
     * @param BuilderFactory $builderFactory
     * @param Checkout       $checkout
     * @param EventManager   $eventManager
     * @param array          $data
     */
    public function __construct(
        CheckoutHelper $checkoutHelper,
        Kco $kco,
        BuilderFactory $builderFactory,
        Checkout $checkout,
        EventManager $eventManager,
        $data = []
    ) {
        parent::__construct($data);
        $this->checkoutHelper = $checkoutHelper;
        $this->kco = $kco;
        $this->builderFactory = $builderFactory;
        $this->checkout = $checkout;
        $this->eventManager = $eventManager;
        $this->builderType = KasperBuilder::class;
    }

    /**
     * Create or update an order in the checkout API
     *
     * @param string $checkoutId
     * @param bool|false $createIfNotExists
     * @param bool|false $updateItems
     *
     * @return DataObject
     * @throws KlarnaException
     */
    public function initKlarnaCheckout($checkoutId = null, $createIfNotExists = false, $updateItems = false)
    {
        $api = $this->_getCheckoutApi();
        $klarnaOrder = new DataObject();

        if ($createIfNotExists || $updateItems) {
            $data = $this->getGeneratedCreateRequest();

            if (!$checkoutId && $createIfNotExists) {
                $klarnaOrder = $api->createOrder($data);
            } elseif ($updateItems) {
                $klarnaOrder = $api->updateOrder($checkoutId, $data);
            }
        } elseif ($checkoutId) {
            $klarnaOrder = $api->getOrder($checkoutId);
        }
        if (is_array($klarnaOrder)) {
            $klarnaOrder = new DataObject($klarnaOrder);
        }

        // If existing order fails or is expired, create a new one
        if ($createIfNotExists
            && ('READ_ONLY_ORDER' == $klarnaOrder->getErrorCode()
                || !$klarnaOrder->getErrorCode()) && !$klarnaOrder->getIsSuccessful()
        ) {
            $data = $this->getGeneratedCreateRequest();
            $klarnaOrder = $api->createOrder($data);
            if (is_array($klarnaOrder)) {
                $klarnaOrder = new DataObject($klarnaOrder);
            }
        }

        // If we still get an error, give up
        if (!$klarnaOrder->getIsSuccessful()) {
            if ($klarnaOrder->getResponseStatusCode() == 401) {
                throw new KlarnaApiException(__($klarnaOrder->getResponseStatusMessage()));
            }
            throw new KlarnaException(__('Unable to initialize Klarna checkout order'));
        }

        $this->setKlarnaOrder($klarnaOrder);

        return $klarnaOrder;
    }

    /**
     * Get the api for checkout api
     *
     * @return Checkout
     */
    protected function _getCheckoutApi()
    {
        return $this->checkout;
    }

    /**
     * Get the html snippet for an order
     *
     * @return string
     */
    public function getKlarnaCheckoutGui()
    {
        return $this->getKlarnaOrder()->getHtmlSnippet();
    }
}
