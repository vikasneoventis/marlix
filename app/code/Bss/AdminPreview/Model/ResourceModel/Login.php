<?php
/**
 * BssCommerce
 * AdminPreview
 */

namespace Bss\AdminPreview\Model\ResourceModel;

/**
 * LoginAsCustomer resource model
 */
class Login extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_adminpreview_login_as_customer', 'login_id');
    }

}
