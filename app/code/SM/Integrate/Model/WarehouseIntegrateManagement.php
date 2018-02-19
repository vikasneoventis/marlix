<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/10/17
 * Time: 11:34 AM
 */

namespace SM\Integrate\Model;


use SM\Integrate\Data\XWarehouse;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

class WarehouseIntegrateManagement extends ServiceAbstract {

    static $WAREHOUSE_ID = null;

    /**
     * @var array
     */
    static $LIST_WH_INTEGRATE
        = [
            'ahead_works' => [
                [
                    "version" => "~1.0.0",
                    "class"   => "SM\\Integrate\\RewardPoint\\AheadWorks100"
                ]
            ],
            'mage_store'  => [
                [
                    'version' => "~1.1.1",
                    "class"   => "SM\\Integrate\\Warehouse\\Magestore111"
                ]
            ],
        ];
    /**
     * @var \SM\Integrate\Warehouse\Contract\WarehouseIntegrateInterface
     */
    private $_currentIntegrateModel;
    /**
     * @var \SM\Integrate\Model\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \SM\Integrate\Helper\Data
     */
    private $integrateData;

    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \SM\Integrate\Helper\Data $integrateData
    ) {
        $this->integrateData = $integrateData;
        $this->objectManager = $objectManager;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return \SM\Integrate\Warehouse\Contract\WarehouseIntegrateInterface
     */
    public function getCurrentIntegrateModel() {
        if (is_null($this->_currentIntegrateModel)) {
            // FIXME: do something to get current integrate class
            $class = self::$LIST_WH_INTEGRATE['mage_store'][0]['class'];

            $this->_currentIntegrateModel = $this->objectManager->create($class);
        }

        return $this->_currentIntegrateModel;
    }

    /**
     * @return array
     */
    public function getList() {
        return $this->loadWarehouse($this->getSearchCriteria())->getOutput();
    }

    /**
     * @param $searchCriteria
     *
     * @return \SM\Core\Api\SearchResult
     */
    public function loadWarehouse($searchCriteria) {
        $collection = $this->getCurrentIntegrateModel()->getWarehouseCollection($searchCriteria);

        $items = [];
        if (!$this->integrateData->isIntegrateWH() || $collection->getLastPageNumber() < $searchCriteria->getData('currentPage')) {
        }
        else {
            foreach ($collection as $item) {
                $xWarehouse = new XWarehouse($item->getData());

                $items[] = $xWarehouse;
            }
        }

        return $this->getSearchResult()
                    ->setItems($items)
                    ->setTotalCount($collection->getSize())
                    ->setLastPageNumber($collection->getLastPageNumber());
    }

    public function getStockItem($product, $warehouseId) {
        return $this->getCurrentIntegrateModel()->getStockItem($product, $warehouseId);
    }
}