<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_DuplicateCategories
 */

namespace Amasty\DuplicateCategories\Helper;

use Magento\Framework\App\ResourceConnection;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_objectManager;
    protected $_authSession;
    protected $_sessionManager;
    protected $_eavConfig;
    protected $_connection;
    protected $_resource;
    protected $_storeManager;


    public function __construct(
        ResourceConnection $resource,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context
    )
    {
        parent::__construct($context);
        $this->_objectManager = $objectManager;
        $this->_authSession = $authSession;
        $this->_sessionManager = $sessionManager;
        $this->_eavConfig = $eavConfig;
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection();
    }

    public function getSearchReplaceCnt()
    {
        return 10;
    }
}
