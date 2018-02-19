<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/10/17
 * Time: 3:09 PM
 */

namespace SM\Integrate\Warehouse\Contract;


use Magento\Framework\ObjectManagerInterface;

/**
 * Class AbstractWarehouseIntegrate
 *
 * @package SM\Integrate\Warehouse\Contract
 */
abstract class AbstractWarehouseIntegrate {

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * AbstractWarehouseIntegrate constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }
}