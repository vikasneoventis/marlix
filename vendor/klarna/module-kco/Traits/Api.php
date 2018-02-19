<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Traits;

use Klarna\Core\Api\BuilderInterface;
use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Model\Api\BuilderFactory;
use Klarna\Kco\Model\Checkout\Type\Kco;
use Magento\Framework\DataObject;
use Magento\Store\Model\Store;

/**
 * Klarna api integration abstract
 *
 * @method setStore(Store $store)
 * @method Store getStore()
 * @method setConfig(DataObject $config)
 * @method DataObject getConfig()
 */
trait Api
{
    /**
     * @var DataObject
     */
    protected $klarnaOrder = null;

    /**
     * API type code
     *
     * @var string
     */
    protected $builderType = '';

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Kco
     */
    protected $kco;

    /**
     * @var BuilderFactory
     */
    protected $builderFactory;

    /**
     * Get Klarna Checkout Reservation Id
     *
     * @return string
     */
    public function getReservationId()
    {
        return $this->getKlarnaOrder()->getOrderId();
    }

    /**
     * Get Klarna checkout order details
     *
     * @return DataObject
     */
    public function getKlarnaOrder()
    {
        if ($this->klarnaOrder === null) {
            $this->klarnaOrder = new DataObject();
        }

        return $this->klarnaOrder;
    }

    /**
     * Set Klarna checkout order details
     *
     * @param DataObject $klarnaOrder
     *
     * @return $this
     */
    public function setKlarnaOrder(DataObject $klarnaOrder)
    {
        $this->klarnaOrder = $klarnaOrder;

        return $this;
    }

    /**
     * Get generated create request
     *
     * @return array
     * @throws KlarnaException
     */
    public function getGeneratedCreateRequest()
    {
        return $this->_getGenerator()
                    ->setObject($this->getQuote())
                    ->generateRequest(BuilderInterface::GENERATE_TYPE_CREATE)
                    ->getRequest();
    }

    /**
     * Get current quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if ($this->hasData('quote')) {
            return $this->getData('quote');
        }

        return $this->_getQuote();
    }

    /**
     * Get one page checkout model
     *
     * @return Kco
     */
    public function getKco()
    {
        return $this->kco;
    }

    /**
     * Get generated update request
     *
     * @return array
     * @throws KlarnaException
     */
    public function getGeneratedUpdateRequest()
    {
        return $this->_getGenerator()
                    ->setObject($this->getQuote())
                    ->generateRequest(BuilderInterface::GENERATE_TYPE_UPDATE)
                    ->getRequest();
    }

    /**
     * Get Klarna checkout helper
     *
     * @return Checkout
     */
    public function getConfigHelper()
    {
        return $this->configHelper;
    }

    /**
     * Get request generator
     *
     * @return \Klarna\Kco\Model\Api\Builder\AbstractModel
     * @throws KlarnaException
     */
    protected function _getGenerator()
    {
        return $this->builderFactory->create($this->builderType);
    }

    /**
     * Get current active quote instance
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote()
    {
        $this->setData('quote', $this->getKco()->getQuote());
        return $this->getData('quote');
    }
}
