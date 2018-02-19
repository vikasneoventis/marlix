<?php

namespace SM\Performance\Observer;


use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\StoreManagerInterface;
use SM\Performance\Helper\RealtimeManager;

/**
 * Class ModelAfterSave
 *
 * @package SM\Performance\Observer
 */
class ModelAfterSave implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \SM\Performance\Helper\RealtimeManager
     */
    private $realtimeManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $cache;

    /**
     * ModelAfterSave constructor.
     *
     * @param \SM\Performance\Helper\RealtimeManager $realtimeManager
     */
    public function __construct(
        RealtimeManager $realtimeManager,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Cache\FrontendInterface $cache
    ) {
        $this->storeManager    = $storeManager;
        $this->realtimeManager = $realtimeManager;
        $this->cache           = $cache;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $object = $observer->getData('object');

        if ($object instanceof Customer)
            $this->realtimeManager->trigger(RealtimeManager::CUSTOMER_ENTITY, $object->getId(), RealtimeManager::TYPE_CHANGE_UPDATE);

        if ($object instanceof Category)
            $this->realtimeManager->trigger(RealtimeManager::CATEGORY_ENTITY, $object->getId(), RealtimeManager::TYPE_CHANGE_UPDATE);

        if ($object instanceof \Magento\Customer\Model\Group)
            $this->realtimeManager->trigger(
                RealtimeManager::CUSTOMER_GROUP,
                $object->getData('customer_group_id'),
                RealtimeManager::TYPE_CHANGE_UPDATE);

        if ($object instanceof Product) {
            $ids = [];
            array_push($ids, $object->getId());
            if ($object->getTypeId() == 'configurable') {
                /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $instanceType */
                $instanceType = $object->getTypeInstance();
                $childIds     = $instanceType->getChildrenIds($object->getId());
                foreach ($childIds as $_ids) {
                    $ids = array_merge($ids, $_ids);
                }
            }
            $this->realtimeManager->trigger(RealtimeManager::PRODUCT_ENTITY, join(",", array_unique($ids)), RealtimeManager::TYPE_CHANGE_UPDATE);
        }

        //$file   = 'ob.txt';
        //$person = get_class($object) . "\n";
        //file_put_contents($file, $person, FILE_APPEND | LOCK_EX);

        if (\SM\Sales\Repositories\OrderManagement::$SAVE_ORDER === true && $object instanceof \Magento\Quote\Model\Quote\Item) {
            $this->realtimeManager->trigger(
                RealtimeManager::PRODUCT_ENTITY,
                $object->getProduct()->getId(),
                RealtimeManager::TYPE_CHANGE_UPDATE);
        }

        if ($object instanceof \Magento\CatalogInventory\Model\Stock\Item) {
            $this->realtimeManager->trigger(
                RealtimeManager::PRODUCT_ENTITY,
                $object->getData('product_id'),
                RealtimeManager::TYPE_CHANGE_UPDATE);
        }
    }
}
