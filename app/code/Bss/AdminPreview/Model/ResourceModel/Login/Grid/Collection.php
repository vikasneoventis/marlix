<?php
/**
 * BssCommerce
 * AdminPreview
 */

namespace Bss\AdminPreview\Model\ResourceModel\Login\Grid;

/**
 * LoginAsCustomer collection
 */
class Collection extends \Bss\AdminPreview\Model\ResourceModel\Login\Collection
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
        $this->_map['fields']['email'] = 'c.email';
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(
                array('c' => $this->getTable('customer_entity')),
                'c.entity_id = main_table.customer_id',
                array('email')
            )->joinLeft(
                array('a' => $this->getTable('admin_user')),
                'a.user_id = main_table.admin_id',
                array('username')
            );
        return $this;
    }

}
