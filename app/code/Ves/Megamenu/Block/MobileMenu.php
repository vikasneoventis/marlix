<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2017 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Block;

class MobileMenu extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $_helper;

    /**
     * @var \\Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context         
     * @param \Ves\Megamenu\Helper\Data                        $helper          
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager   
     * @param \Magento\Customer\Model\Session                  $customerSession 
     * @param array                                            $data            
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ves\Megamenu\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helper          = $helper;
        $this->_objectManager   = $objectManager;
        $this->_customerSession = $customerSession;
    }

    public function _toHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate("Ves_Megamenu::mobile_menu.phtml");
        }

        $store = $this->_storeManager->getStore();
        $html  = $menu = '';
        $tmp   = $this->_objectManager->create('\Ves\Megamenu\Model\Menu'); 

        if ($menuId = $this->getData('id')) {
            $menu = $tmp->setStore($store)->load((int)$menuId);
        } elseif ($alias = $this->getData('alias')) {
            $menu = $tmp->setStore($store)->load(addslashes($alias));
        }
        if ($menu) {
            $customerGroups = $menu->getData('customer_group_ids');
            $customerGroupId = (int)$this->_customerSession->getCustomerId();
            if ($customerGroupId) {
                if(!in_array($customerGroupId, $customerGroups)) return;
            }
        }
        if ($menu && $menu->getStatus()) {
            $this->setData("menu", $menu);
        }
        return parent::_toHtml();
    }
}
