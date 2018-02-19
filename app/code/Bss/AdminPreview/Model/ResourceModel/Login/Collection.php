<?php
/**
 * BssCommerce
 * AdminPreview
 */

namespace Bss\AdminPreview\Model\ResourceModel\Login;

/**
 * LoginAsCustomer collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Bss\AdminPreview\Model\Login', 'Bss\AdminPreview\Model\ResourceModel\Login');
    }

}
