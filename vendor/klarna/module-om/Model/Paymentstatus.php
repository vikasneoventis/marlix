<?php
/**
 * Paymentstatus
 *
 * @copyright Copyright © 2017 Klarna Bank AB. All rights reserved.
 * @author    Joe Constant <joe.constant@klarna.com>
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Ordermanagement\Model;

use Klarna\Core\Api\OrderInterface;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Ordermanagement\Model\Api\Factory;
use Klarna\Ordermanagement\Model\Api\Ordermanagement;
use Magento\Framework\DataObject;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;

class Paymentstatus
{
    /**
     * @var Ordermanagement
     */
    protected $om;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Factory
     */
    protected $omFactory;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * AddOrderDetailsOnPush constructor.
     *
     * @param Factory                  $omFactory
     * @param ConfigHelper             $configHelper
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(Factory $omFactory, ConfigHelper $configHelper, OrderRepositoryInterface $orderRepository)
    {
        $this->omFactory = $omFactory;
        $this->configHelper = $configHelper;
        $this->orderRepository = $orderRepository;
    }

    public function getStatusUpdate(OrderInterface $klarnaOrder)
    {

        $order = $this->orderRepository->get($klarnaOrder->getOrderId());
        $this->configureOm($order->getStoreId());

        try {
            return $this->om->getPlacedKlarnaOrder($klarnaOrder->getKlarnaOrderId());
        } catch (\Exception $e) {
            // what now?
            $do = new DataObject([
                'status' => 'ERROR',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ]);
            if (method_exists($e, 'getPayload')) {
                $do->setData('payload', $e->getPayload());
            }
            return $do;
        }
    }

    /**
     * Configure OM for store
     *
     * @param StoreInterface|int|string $store
     */
    protected function configureOm($store)
    {
        $omClass = $this->configHelper->getOrderMangagementClass($store);
        $this->om = $this->omFactory->create($omClass);
        $this->om->resetForStore($store, 'klarna_kco'); // Push only used for KCO
    }
}
