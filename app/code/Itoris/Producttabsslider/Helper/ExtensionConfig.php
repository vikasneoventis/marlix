<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_TABS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\Producttabsslider\Helper;
/**
 * Created by PhpStorm.
 * User: Workstation1
 * Date: 11.04.2016
 * Time: 19:48
 */
class ExtensionConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SCOPE_TYPE_STORES = 'stores';

    protected $settings = [];
    protected $messageManager;
    protected $_storeManager;
    protected $_objectManager;
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        $this->messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->_objectManager = $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_coreRegistry = $registry;
        $this->_backendConfig = $backendConfig;
        $this->_localeDate = $localeDate;
        $this->_customerSession = $customerSession;
        $this->_scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        parent::__construct($context);
    }

    public function isEnabled() {
        return (int)$this->_backendConfig->getValue('itoris_producttabsslider/general/enabled') && !$this->isDisabledForStore()
        && count(explode('|', $this->_backendConfig->getValue('itoris_core/installed/Itoris_Producttabsslider'))) == 2;
    }

    public function isDisabledForStore(){
        return !(bool)$this->_scopeConfig->getValue('itoris_producttabsslider/general/enabled', self::SCOPE_TYPE_STORES, $this->_storeManager->getStore()->getId());
    }
}